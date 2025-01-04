<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Field
{
    public const TYPE_S = 'S'; // – String
    public const TYPE_N = 'N'; // – Number
    public const TYPE_B = 'B'; // – Binary
    public const TYPE_BOOL = 'BOOL'; // – Boolean
    public const TYPE_NULL = 'NULL'; // – Null
    public const TYPE_M = 'M'; // – Map
    public const TYPE_L = 'L'; // – List
    public const TYPE_SS = 'SS'; // – String Set
    public const TYPE_NS = 'NS'; // – Number Set
    public const TYPE_BS = 'BS'; // – Binary Set

    public function __construct(
        public ?string $type = null,
        public ?string $name = null,
        public ?bool $format = null,
    ) {}
}
