<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function systems(): BelongsToMany
    {
        return $this->belongsToMany(
            System::class,
            'SystemOrganizations',
            'OrganizationID',
            'SystemID',
            'ID',
            'ID',
        );
    }
}
