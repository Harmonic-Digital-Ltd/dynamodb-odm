<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Model;

use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Attribute\Item;
use HarmonicDigital\DynamodbOdm\Attribute\PartitionKey;

#[Item]
class TestEmbeddedObject
{
    public function __construct(
        #[Field]
        #[PartitionKey]
        public string $id,
        #[Field]
        public EmbeddedItem $embeddedItem,
    ) {}
}
