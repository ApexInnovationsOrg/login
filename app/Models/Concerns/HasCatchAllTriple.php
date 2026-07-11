<?php

namespace App\Models\Concerns;

use App\Saml\RoutingOperator;

trait HasCatchAllTriple
{
    /** The reserved * / wildcard / * triple: matches every login. */
    public function isCatchAll(): bool
    {
        return $this->attribute === '*'
            && $this->operator === RoutingOperator::Wildcard
            && $this->value === '*';
    }
}
