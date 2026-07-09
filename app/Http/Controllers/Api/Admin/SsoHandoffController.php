<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Saml\AdminSsoHandoff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SsoHandoffController extends Controller
{
    /**
     * Exchange a single-use admin SSO token for the Employee identity.
     * Called server-to-server by website_admin/ssoLogon.php; the token
     * arriving in a browser URL is worthless without the bearer token.
     */
    public function redeem(Request $request, AdminSsoHandoff $handoff): JsonResponse
    {
        $payload = $handoff->redeem((string) $request->input('token', ''));

        abort_if($payload === null, 404);

        return response()->json(['data' => $payload]);
    }
}
