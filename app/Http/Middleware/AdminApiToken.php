<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminApiToken
{
    /**
     * The token authorizes; X-Acting-Admin only attributes (audit).
     * Missing configuration fails closed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('admin.api_token');

        if (empty($expected)) {
            Log::error('ADMIN_API_TOKEN is not configured; admin API request refused');

            return response()->json(['message' => 'Admin API is not configured.'], 503);
        }

        if (! hash_equals((string) $expected, (string) $request->bearerToken())) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $isMutation = in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);

        if ($isMutation && trim((string) $request->header('X-Acting-Admin', '')) === '') {
            return response()->json(['message' => 'X-Acting-Admin header is required.'], 400);
        }

        return $next($request);
    }
}
