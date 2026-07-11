<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class System extends LegacyModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'Systems';

    protected $fillable = ['Name', 'CreationDate'];

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(
            Organization::class,
            'SystemOrganizations',
            'SystemID',
            'OrganizationID',
            'ID',
            'ID',
        );
    }
}
