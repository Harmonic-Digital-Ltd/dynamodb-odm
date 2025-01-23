<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class SortKey extends Key
{
    public function __construct(
        string $keyType = 'RANGE',
    ) {
        parent::__construct($keyType);
    }
}
