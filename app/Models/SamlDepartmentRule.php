<?php

namespace App\Models;

use App\Models\Concerns\HasCatchAllTriple;
use App\Saml\RoutingOperator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SamlDepartmentRule extends Model
{
    use HasCatchAllTriple;
    use HasFactory;

    protected $fillable = ['saml_client_id', 'position', 'attribute', 'operator', 'value', 'department_name'];

    protected $casts = [
        'position' => 'integer',
        'operator' => RoutingOperator::class,
    ];
}
