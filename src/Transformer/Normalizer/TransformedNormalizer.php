<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Transformer\Normalizer;

use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TransformedNormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!$data instanceof PrenormalizedValue) {
            throw new LogicException('The data must be an instance of PrenormalizedValue');
        }

        return $data->value;
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): bool {
        return $data instanceof PrenormalizedValue;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => true];
    }
}
