<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'known_attributes',
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

    public function orgRules(): HasMany
    {
        return $this->hasMany(SamlOrgRule::class)->orderBy('position');
    }

    public function departmentRules(): HasMany
    {
        return $this->hasMany(SamlDepartmentRule::class)->orderBy('position');
    }

    public function attributeObservations(): HasMany
    {
        return $this->hasMany(SamlAttributeObservation::class);
    }

    /**
     * A null json column casts to null, not []; normalize so consumers can
     * always iterate. (The array cast alone returns null for a null column
     * in Laravel 12.)
     */
    public function getKnownAttributesAttribute($value): array
    {
        return $value === null ? [] : (array) json_decode($value, true);
    }

    /**
     * Ensure arrays serialize to json on save, mirroring what the removed
     * array cast did on write, so read+write conversion is explicit and paired.
     */
    public function setKnownAttributesAttribute($value): void
    {
        $this->attributes['known_attributes'] = json_encode(array_values((array) $value));
    }

    /**
     * SsoGrant rows for this client's owner tuple. Not a real Eloquent
     * relation: SsoGrant.owner_id references SamlClient.owner_id (the
     * polymorphic-by-hand owner tuple), not SamlClient.id, so a plain
     * hasMany('owner_id') would silently join on the wrong column. See
     * SsoGrant::scopeForOwner().
     *
     * @return Builder<SsoGrant>
     */
    public function grants(): Builder
    {
        return SsoGrant::forOwner($this);
    }
}
