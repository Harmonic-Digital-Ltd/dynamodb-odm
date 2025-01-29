<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Transformer;

use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MapableTransformer implements DenormalizerInterface, NormalizerInterface
{
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

    public function normalize(
        mixed $data,
        ?string $format = null,
        array $context = []
    ): ?array {
        if (null === $data) {
            return null;
        }

        if (!$data instanceof Mapable) {
            throw new LogicException('The type must implement Mapable');
        }

        return $data->toMap();
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Mapable || null === $data;
    }

    /** @phpstan-assert-if-true class-string<Mapable> $className */
    private static function isMapable(string $className): bool
    {
        return is_subclass_of($className, Mapable::class);
    }
}
