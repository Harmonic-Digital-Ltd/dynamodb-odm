<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class Item
{
    public function __construct(
        public ?string $tableName = null,
        public int $readCapacityUnits = 5,
        public int $writeCapacityUnits = 5,
    ) {}
}
