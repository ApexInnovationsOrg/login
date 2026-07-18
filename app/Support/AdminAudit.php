<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminAudit
{
    /**
     * One structured line per admin-API mutation. Field NAMES only, except
     * enabled/email_domains whose values are operationally interesting.
     */
    public static function log(Request $request, string $action, array $context = []): void
    {
        Log::info('admin api: '.$action, [
            'acting_admin' => $request->header('X-Acting-Admin'),
            'method' => $request->method(),
            'path' => $request->path(),
        ] + $context);
    }
}
