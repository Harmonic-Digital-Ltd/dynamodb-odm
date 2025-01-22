<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Model;

use HarmonicDigital\DynamodbOdm\Transformer\Mapable;

class EmbeddedItem implements Mapable
{
    public function __construct(
        public string $name,
        public int $value,
    ) {}

    public static function fromMap(array $data): self
    {
        return new self(
            $data['name'],
            $data['value'],
        );
    }

    public function toMap(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
