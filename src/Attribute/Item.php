<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Item
{
    /**
     * @param null|string $tableName          The table name defaults to the class name
     * @param null|string $partitionKeyPrefix This will be prefixed to the partition key value
     */
    public function __construct(
        public ?string $tableName = null,
        public int $readCapacityUnits = 5,
        public int $writeCapacityUnits = 5,
        public ?string $partitionKeyPrefix = null,
    ) {}
}
