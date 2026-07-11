<?php

namespace App\Saml;

use RuntimeException;

class SamlLoginRejected extends RuntimeException
{
    public function __construct(
        public readonly string $publicMessage,
        public readonly array $logContext = [],
    ) {
        parent::__construct($publicMessage);
    }
}
