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
        'admin_portal',
        'attribute_map',
        'email_domains',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'jit_enabled' => 'boolean',
        'admin_portal' => 'boolean',
        'attribute_map' => 'array',
        'email_domains' => 'array',
    ];

    public function acsUrl(): string
    {
        return url("/saml/{$this->slug}/acs");
    }

    public function metadataUrl(): string
    {
        return url("/saml/{$this->slug}/metadata");
    }

    /**
     * The enabled client claiming this email domain, if any.
     * Domains are stored lowercased; compare lowercased.
     */
    public static function forEmailDomain(string $domain): ?self
    {
        return static::where('enabled', true)
            ->where('admin_portal', false)
            ->whereJsonContains('email_domains', strtolower($domain))
            ->first();
    }
}
