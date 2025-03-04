<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Transformer\Normalizer;

final readonly class PredenormalizedValue
{
    public function __construct(
        public mixed $value,
    ) {}
}
