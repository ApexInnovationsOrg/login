<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    public $timestamps = false;

    protected $table = 'Departments';
    use HasFactory;

    public function org()
    {
        return $this->hasOne(Organization::class,'ID','OrganizationID');
    }
}
