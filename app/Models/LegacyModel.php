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
 */
abstract class LegacyModel extends Model
{
    protected $primaryKey = 'ID';
}
