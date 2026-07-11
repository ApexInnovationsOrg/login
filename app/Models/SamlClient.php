<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'owner_type',
        'owner_id',
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
        'owner_id' => 'integer',
    ];

    public function acsUrl(): string
    {
        return url("/saml/{$this->slug}/acs");
    }

    public function metadataUrl(): string
    {
        return url("/saml/{$this->slug}/metadata");
    }

    public function ownedByOrganization(): bool
    {
        return $this->owner_type === 'organization';
    }

    public function ownerName(): ?string
    {
        return $this->ownedByOrganization()
            ? Organization::where('ID', $this->owner_id)->value('Name')
            : System::where('ID', $this->owner_id)->value('Name');
    }

    /**
     * The organizations this client may place or grant users in: the owning
     * org, or every org of the owning system. An organization belongs to one
     * system by business rule; SystemOrganizations doesn't enforce it, so
     * select tolerantly.
     *
     * @return array<int, int>
     */
    public function scopedOrganizationIds(): array
    {
        if ($this->ownedByOrganization()) {
            return [$this->owner_id];
        }

        return DB::table('SystemOrganizations')
            ->where('SystemID', $this->owner_id)
            ->pluck('OrganizationID')
            ->map(fn ($id) => (int) $id)
            ->all();
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
