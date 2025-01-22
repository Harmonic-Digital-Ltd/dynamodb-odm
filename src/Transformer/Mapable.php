<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Transformer;

interface Mapable
{
    public static function fromMap(array $data): self;

    public function toMap(): array;
}
