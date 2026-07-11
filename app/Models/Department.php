<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends LegacyModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['ID'];

    protected $table = 'Departments';

    // Existing relation — used by User::getPasswordRequirements() via department->org.
    // Do not change.
    public function org()
    {
        return $this->hasOne(Organization::class, 'ID', 'OrganizationID');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'OrganizationID', 'ID');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'DepartmentID', 'ID');
    }
}
