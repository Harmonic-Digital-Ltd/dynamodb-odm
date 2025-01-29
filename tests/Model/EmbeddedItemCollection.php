<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Model;

use HarmonicDigital\DynamodbOdm\Transformer\Mapable;

class EmbeddedItemCollection implements Mapable
{
    /** @param list<EmbeddedItem> $items */
    public function __construct(
        public array $items,
    ) {}

    public static function fromMap(array $data): self
    {
        return new self(
            \array_map(fn (array $item): EmbeddedItem => EmbeddedItem::fromMap($item), $data['items']),
        );
    }

    public function toMap(): array
    {
        return [
            'items' => \array_map(fn (EmbeddedItem $item): array => $item->toMap(), $this->items),
        ];
    }
}
