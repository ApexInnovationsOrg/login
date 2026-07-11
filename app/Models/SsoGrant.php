<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SsoGrant extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'owner_type', 'owner_id', 'granted_by'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'ID');
    }

    /**
     * Grants belonging to a SamlClient's owner tuple (owner_type + owner_id).
     * A plain hasMany on SamlClient would join on SamlClient.id, which is
     * not what SsoGrant.owner_id references — this scope is the correct
     * "relation" between the two.
     *
     * @param  Builder<SsoGrant>  $query
     * @return Builder<SsoGrant>
     */
    public function scopeForOwner(Builder $query, SamlClient $client): Builder
    {
        return $query->where('owner_type', $client->owner_type)->where('owner_id', $client->owner_id);
    }
}
