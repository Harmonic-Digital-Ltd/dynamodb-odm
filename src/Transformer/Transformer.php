<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Transformer;

use HarmonicDigital\DynamodbOdm\Transformer\Exception\TransformationException;

interface Transformer
{
    /**
     * @throws TransformationException
     */
    public function toDatabase(mixed $value, \ReflectionProperty $property): null|array|bool|float|int|object|string;

    /**
     * @throws TransformationException
     */
    public function fromDatabase(null|array|bool|float|int|object|string $value, \ReflectionProperty $property): mixed;
}
