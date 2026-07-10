<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SamlClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SsoLookupController extends Controller
{
    /**
     * Route an email to its organization's SP-initiated SSO endpoint.
     *
     * Every non-match — unknown domain, disabled client, malformed input —
     * returns the identical {"sso": null} so the endpoint cannot be used to
     * enumerate which organizations use SSO.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $email = is_string($request->input('email')) ? $request->input('email') : '';
        $domain = str_contains($email, '@') ? Str::afterLast($email, '@') : '';

        $client = $domain === '' ? null : SamlClient::forEmailDomain($domain);

        return response()->json([
            'sso' => $client ? route('saml.login', $client->slug, absolute: false) : null,
        ]);
    }
}
