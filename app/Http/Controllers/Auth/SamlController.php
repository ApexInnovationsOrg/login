<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SamlClient;
use App\Models\User;
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

        try {
            $user = $this->provisioner->provision($client, $email, $firstName, $lastName);
        } catch (SamlLoginRejected $e) {
            return $this->reject($client, $e->logContext, $e->publicMessage);
        }

        // Spec: warn while assertions still validate but the IdP cert nears expiry
        $certStatus = app(SamlClientManager::class)->certificateStatus($client);
        if ($certStatus['expiring']) {
            Log::warning('SAML client IdP certificate expires soon', [
                'client' => $client->slug,
                'expires_at' => $certStatus['expires_at']?->toDateString(),
            ]);
        }

        $this->establishSession($request, $user, $client);

        if ($user->DepartmentID == null || $user->CredentialID == null) { // loose: matches 0
            return redirect('/finishAccountCreation');
        }

        return redirect()->away(config('app.mycurriculum_url'));
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

    private function establishSession(Request $request, User $user, SamlClient $client): void
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
        // finishAccountCreation lists departments from this key
        $request->session()->put('Organization', $client->organization_id);
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
