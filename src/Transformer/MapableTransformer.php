<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Transformer;

use HarmonicDigital\DynamodbOdm\Transformer\Exception\TransformationException;

class MapableTransformer implements Transformer
{
    /** @phpstan-assert-if-true \ReflectionNamedType<Mapable> $type */
    public static function supportsType(?\ReflectionType $type): bool
    {
        if (!$type instanceof \ReflectionNamedType) {
            return false;
        }

        if (!is_subclass_of($type->getName(), Mapable::class)) {
            return false;
        }

        return true;
    }

    public function toDatabase(mixed $value, \ReflectionProperty $property): ?array
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Mapable) {
            throw new TransformationException('Value must implement Mapable');
        }

        return $value->toMap();
    }

    public function fromDatabase(null|array|bool|float|int|string $value, \ReflectionProperty $property): ?Mapable
    {
        if (null === $value) {
            return null;
        }

        $type = $property->getType();

        if (!self::supportsType($type)) {
            throw new TransformationException('The type of the property must implement Mapable');
        }

        if (!\is_array($value)) {
            throw new TransformationException('Value must be an array');
        }

        return $type->getName()::fromMap($value);
    }
}
