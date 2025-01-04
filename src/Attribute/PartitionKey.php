<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class PartitionKey extends Key
{
    public function __construct(
        string $keyType = 'HASH',
    ) {
        parent::__construct($keyType);
    }
}
