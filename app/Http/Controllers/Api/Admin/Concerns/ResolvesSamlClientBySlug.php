<?php

namespace App\Http\Controllers\Api\Admin\Concerns;

use App\Models\SamlClient;

trait ResolvesSamlClientBySlug
{
    private function resolve(string $slug): SamlClient
    {
        $client = SamlClient::where('slug', $slug)->first();

        abort_if($client === null, 404);

        return $client;
    }
}
