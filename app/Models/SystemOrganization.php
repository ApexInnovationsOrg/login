<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Join row between Systems and Organizations. The table allows many systems
 * per org, but the business rule is one: write through updateOrCreate keyed
 * on OrganizationID (see OrganizationFactory::forSystem()).
 */
class SystemOrganization extends Pivot
{
    public $timestamps = false;

    public $incrementing = true;

    protected $table = 'SystemOrganizations';

    protected $primaryKey = 'ID';

    protected $fillable = ['SystemID', 'OrganizationID'];
}
