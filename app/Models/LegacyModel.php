<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Base for models mapping onto the pre-Laravel shared schema: every such table's
 * primary key is an unsigned-int column named `ID`. Timestamps are NOT set here —
 * a few legacy tables (ProfessionalRoles, ProfessionalCredentialFilters) do carry
 * created_at/updated_at, so each model declares its own $timestamps.
 *
 * App-owned tables (e.g. SamlClient) use stock Laravel conventions and extend Model.
 *
 * Note: renaming $primaryKey does not make Eloquent alias the plain `id` property
 * to it (there is no such mechanism in the framework) — since none of these tables
 * have an `id` column, we add the accessor explicitly so `->id` resolves the same
 * way `getKey()` does.
 */
abstract class LegacyModel extends Model
{
    protected $primaryKey = 'ID';

    public function getIdAttribute()
    {
        return $this->attributes[$this->getKeyName()] ?? null;
    }
}
