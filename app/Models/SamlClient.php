<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SamlClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'enabled',
        'idp_entity_id',
        'idp_sso_url',
        'idp_certificate',
        'organization_id',
        'department_id',
        'jit_enabled',
        'attribute_map',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'jit_enabled' => 'boolean',
        'attribute_map' => 'array',
    ];

    public function acsUrl(): string
    {
        return url("/saml/{$this->slug}/acs");
    }

    public function metadataUrl(): string
    {
        return url("/saml/{$this->slug}/metadata");
    }
}
