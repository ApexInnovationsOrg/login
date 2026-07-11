<?php

namespace App\Models;

use App\Saml\RoutingOperator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SamlOrgRule extends Model
{
    use HasFactory;

    protected $fillable = ['saml_client_id', 'position', 'attribute', 'operator', 'value', 'organization_id'];

    protected $casts = [
        'position' => 'integer',
        'organization_id' => 'integer',
        'operator' => RoutingOperator::class,
    ];

    /** The reserved * / wildcard / * triple: matches every login. */
    public function isCatchAll(): bool
    {
        return $this->attribute === '*'
            && $this->operator === RoutingOperator::Wildcard
            && $this->value === '*';
    }
}
