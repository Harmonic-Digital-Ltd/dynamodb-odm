<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Attribute;

abstract readonly class Key
{
    public function __construct(
        public string $keyType,
    ) {}
}
