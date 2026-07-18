<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Organization extends LegacyModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['ID'];

    protected $table = 'Organizations';

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'OrganizationID', 'ID');
    }

    public function system(): HasOneThrough
    {
        return $this->hasOneThrough(
            System::class,
            SystemOrganization::class,
            'OrganizationID', // FK on SystemOrganizations → Organizations.ID
            'ID',             // key on Systems matched to SystemOrganizations.SystemID
            'ID',             // local key on Organizations
            'SystemID',
        );
    }
}
