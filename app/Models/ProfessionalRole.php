<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfessionalRole extends LegacyModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'ProfessionalRoles';
}
