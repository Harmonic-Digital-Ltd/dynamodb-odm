<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Parser;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

interface FieldParserInterface
{
    /** @return array<Field::TYPE_*, mixed> */
    public function toDynamoDb(MappedField $field, mixed $value): array;

    public function dynamoDbToPropertyArray(array $item, MappedItem $mappedItem): array;

    public function getNormalizer(): DenormalizerInterface&NormalizerInterface;
}
