<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SamlClient;
use App\Models\User;
use App\Saml\AdminSsoHandoff;
use App\Saml\AttributeRouter;
use App\Saml\KnownAttributeCollector;
use App\Saml\SamlClientManager;
use App\Saml\SamlLoginRejected;
use App\Saml\SamlSettingsFactory;
use App\Saml\SamlUserProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OneLogin\Saml2\Auth as OneLoginAuth;
use OneLogin\Saml2\Settings;

class SamlController extends Controller
{
    public function __construct(
        private SamlSettingsFactory $settings,
        private SamlUserProvisioner $provisioner,
        private AdminSsoHandoff $adminHandoff,
        private AttributeRouter $router,
        private KnownAttributeCollector $attributeCollector,
    ) {}

    public function acs(Request $request, string $slug)
    {
        $client = SamlClient::where('slug', $slug)->where('enabled', true)->first();

        if (! $client) {
            Log::warning('SAML ACS hit for unknown or disabled client', ['slug' => $slug]);

            abort(404);
        }

        // php-saml reads the raw $_POST superglobal rather than the framework Request,
        // so bridge it explicitly (Laravel's test client does not sync superglobals).
        $_POST['SAMLResponse'] = $request->input('SAMLResponse');

        $auth = new OneLoginAuth($this->settings->forClient($client));
        // php-saml derives "where was this response received" from raw $_SERVER values,
        // which don't reflect the app's configured URL (e.g. behind a proxy, or in tests
        // where $_SERVER isn't populated to match APP_URL). Pin it explicitly.
        $auth->getSettings()->setBaseURL(config('app.url'));
        $auth->processResponse();

        if ($auth->getErrors() !== [] || ! $auth->isAuthenticated()) {
            return $this->reject($client, [
                'reason' => 'invalid_response',
                'errors' => $auth->getErrors(),
                'detail' => $auth->getLastErrorReason(),
            ]);
        }

        if (! $this->consumeAssertionId($auth->getLastAssertionId())) {
            return $this->reject($client, [
                'reason' => 'replayed_assertion',
                'assertion_id' => $auth->getLastAssertionId(),
            ]);
        }

        [$email, $firstName, $lastName] = $this->extractIdentity($auth, $client);

        if ($email === null) {
            return $this->reject($client, ['reason' => 'no_email_attribute']);
        }

        // Record which attribute names this IdP asserts, for the routing rule
        // editor. Names only; the collector skips admin-portal clients and can
        // never break a login (spec: known attributes).
        $this->attributeCollector->capture($client, $auth->getAttributes());

        // Spec: warn while assertions still validate but the IdP cert nears expiry
        $certStatus = app(SamlClientManager::class)->certificateStatus($client);
        if ($certStatus['expiring']) {
            Log::warning('SAML client IdP certificate expires soon', [
                'client' => $client->slug,
                'expires_at' => $certStatus['expires_at']?->toDateString(),
            ]);
        }

        // Admin-portal clients assert Employee identities: no JIT, no Users
        // lookup, no Laravel session — hand off to the portal's own session
        // world via a single-use token (spec: admin portal SSO).
        if ($client->admin_portal) {
            try {
                $redirect = $this->adminHandoff->initiate($client, $email);
            } catch (SamlLoginRejected $e) {
                return $this->reject($client, $e->logContext, $e->publicMessage);
            }

            return redirect()->away($redirect);
        }

        // Deliberately a separate read from the provisioner's own lookup: routing needs the fallback org before the disabled/JIT guards run.
        $existing = User::where('Login', $email)->first();
        $placement = $this->router->route($client, $auth->getAttributes(), $existing?->department?->OrganizationID);

        try {
            $user = $this->provisioner->provision($client, $email, $firstName, $lastName, $placement, $existing);
        } catch (SamlLoginRejected $e) {
            return $this->reject($client, $e->logContext, $e->publicMessage);
        }

        $this->establishSession($request, $user, $client, $placement, $existing === null);

        if ($user->DepartmentID == null || $user->CredentialID == null) { // loose: matches 0
            return redirect('/finishAccountCreation');
        }

        return redirect()->away(config('app.mycurriculum_url'));
    }

    /**
     * SP-initiated login: send the browser to the IdP with an AuthnRequest.
     * The ACS accepts unsolicited assertions (IdP-initiated is a supported
     * flow), so no request-ID state is kept here.
     */
    public function login(string $slug)
    {
        $client = SamlClient::where('slug', $slug)->where('enabled', true)->first();

        if (! $client) {
            Log::warning('SAML SP login hit for unknown or disabled client', ['slug' => $slug]);

            abort(404);
        }

        $auth = new OneLoginAuth($this->settings->forClient($client));
        $auth->getSettings()->setBaseURL(config('app.url'));

        return redirect()->away($auth->login(stay: true));
    }

    /**
     * Atomically claim an assertion ID; false when already seen (replay).
     */
    private function consumeAssertionId(?string $assertionId): bool
    {
        if ($assertionId === null) {
            return false;
        }

        return Cache::store(config('saml.replay_store'))->add(
            'saml:assertion:'.$assertionId,
            1,
            (int) config('saml.replay_ttl'),
        );
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string}
     */
    private function extractIdentity(OneLoginAuth $auth, SamlClient $client): array
    {
        $map = $client->attribute_map;
        $attributes = $auth->getAttributes();
        $value = fn (string $key) => $attributes[$map[$key] ?? ''][0] ?? null;

        $firstName = $value('first_name');
        $lastName = $value('last_name');

        // A mapped name attribute that never arrives means the map and the IdP's
        // emitted attributes disagree. The provisioner substitutes a placeholder
        // on creation, so without this warning a user is silently named "FirstName".
        $missing = array_keys(array_filter([
            'first_name' => $firstName === null,
            'last_name' => $lastName === null,
        ]));

        if ($missing !== []) {
            Log::warning('SAML assertion missing mapped name attribute', [
                'client' => $client->slug,
                'missing' => $missing,
            ]);
        }

        return [
            $value('email') ?? $auth->getNameId() ?: null,
            $firstName,
            $lastName,
        ];
    }

    /**
     * @param  array{organization_id: int, department_id: ?int}|null  $placement
     */
    private function establishSession(Request $request, User $user, SamlClient $client, ?array $placement = null, bool $wasJitCreated = false): void
    {
        Auth::login($user);
        $request->session()->regenerate();

        $user->LastLoginDate = now();
        $user->save();

        // The legacy site reads both spellings of each key
        $request->session()->put('userId', $user->ID);
        $request->session()->put('userID', $user->ID);
        $request->session()->put('userName', $user->FirstName.' '.$user->LastName);
        $request->session()->put('Username', $user->FirstName.' '.$user->LastName);
        // finishAccountCreation lists departments from this key. Org-owned
        // clients offer their org; system-owned users carry their department's
        // org. Edge: an existing dept-less user (DepartmentID 0) on a
        // system-owned client gets null here — FinishUserCreation renders an
        // empty department list until routing (milestone 5) places them.
        // A placement's org only wins when it was actually applied to this
        // user: a resolved department (provisioner moved/placed them there)
        // or a fresh JIT creation (the placement is the only org they've
        // ever had). An existing user whose department rule didn't resolve
        // was left untouched by the provisioner, so the session should
        // reflect their real (unmoved) department's org, not the routed one.
        $placementApplied = $placement !== null && ($placement['department_id'] !== null || $wasJitCreated);
        $request->session()->put('Organization', $placementApplied
            ? $placement['organization_id']
            : ($client->ownedByOrganization() ? $client->owner_id : $user->department?->OrganizationID));
        // dashboard route uses a plain 302 for SAML sessions (IdPs can't follow Inertia 409s)
        $request->session()->put('SAML', true);

        Log::info('SAML login', [
            'client' => $client->slug,
            'user_id' => $user->ID,
        ]);
    }

    public function metadata(string $slug)
    {
        $client = SamlClient::where('slug', $slug)->first();

        if (! $client) {
            abort(404);
        }

        $settings = new Settings($this->settings->forClient($client), spValidationOnly: true);

        return response($settings->getSPMetadata(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function reject(SamlClient $client, array $context, ?string $publicMessage = null)
    {
        Log::warning('SAML login rejected', ['client' => $client->slug] + $context);

        return response()->view('saml.error', [
            'message' => $publicMessage
                ?? 'Something went wrong signing you in. Please try again from your organization\'s portal, or contact your administrator.',
        ], 403);
    }
}
