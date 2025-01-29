<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Transformer;

use HarmonicDigital\DynamodbOdm\Transformer\Exception\TransformationException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class MapableTransformer implements Transformer, DenormalizerInterface
{
    /** @phpstan-assert-if-true \ReflectionNamedType<Mapable> $type */
    public static function supportsType(?\ReflectionType $type): bool
    {
        if (!$type instanceof \ReflectionNamedType) {
            return false;
        }

        return self::isMapable($type->getName());
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

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): bool {
        return \is_array($data) && self::isMapable($type);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Mapable::class => true,
        ];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Mapable
    {
        if (!self::isMapable($type)) {
            throw new LogicException('The type must implement Mapable');
        }

        return $type::fromMap($data);
    }

    /** @phpstan-assert-if-true class-string<Mapable> $className */
    private static function isMapable(string $className): bool
    {
        return is_subclass_of($className, Mapable::class);
    }
}
