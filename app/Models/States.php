<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class States extends LegacyModel
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'States';
}
