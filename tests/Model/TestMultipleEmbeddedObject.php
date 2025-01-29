<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Model;

use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Attribute\Item;
use HarmonicDigital\DynamodbOdm\Attribute\PartitionKey;

#[Item]
class TestMultipleEmbeddedObject
{
    public function __construct(
        #[Field]
        #[PartitionKey]
        public string $id,
        #[Field]
        public EmbeddedItemCollection $embeddedItems = new EmbeddedItemCollection([
            new EmbeddedItem('First', 1),
            new EmbeddedItem('Second', 2),
        ]),
    ) {}
}
