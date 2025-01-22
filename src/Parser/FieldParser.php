<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Parser;

use Aws\DynamoDb\BinaryValue;
use Aws\DynamoDb\Marshaler;
use Aws\DynamoDb\NumberValue;
use HarmonicDigital\DynamodbOdm\Attribute\Field;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final readonly class FieldParser implements FieldParserInterface
{
    public function __construct(
        private Marshaler $marshaller = new Marshaler(),
        private ObjectNormalizer $normalizer = new ObjectNormalizer(),
    ) {}

    /** @return array<Field::TYPE_*, mixed> */
    public function toDynamoDb(MappedField $field, mixed $value): array
    {
        $value = $field->transformToDatabaseValue($value) ?? $value;
        if (\is_object($value) && !$value instanceof BinaryValue && !$value instanceof NumberValue) {
            $value = $this->normalizer->normalize($value);
        }

        return match ($field->getType($value)) {
            Field::TYPE_SS => $this->parseStringSet($value),
            Field::TYPE_NS => $this->parseNumberSet($value),
            Field::TYPE_BS => $this->parseBinarySet($value),
            default => $this->marshaller->marshalValue($value),
        };
    }

    public function dynamoDbToPropertyArray(array $item, MappedItem $mappedItem): array
    {
        $result = [];
        $fields = $mappedItem->getFields();

        /** @var array<string, mixed> $value */
        foreach ($item as $key => $value) {
            $mappedField = $fields[$key];
            $v = $this->marshaller->unmarshalValue($value);
            $v = $fields[$key]->transformFromDatabaseValue($v);
            $result[$mappedField->propertyName] = $v;
        }

        return $result;
    }

    public function getNormalizer(): ObjectNormalizer
    {
        return $this->normalizer;
    }

    /** @return array<Field::TYPE_BS, list<string>> */
    private function parseBinarySet(mixed $value): array
    {
        if (!\is_array($value)) {
            throw new \RuntimeException('Value is not an array: '.$value);
        }

        return [Field::TYPE_BS => \array_values(\array_map(fn ($v) => (string) $v, $value))];
    }

    /** @return array<Field::TYPE_NS, list<string>> */
    private function parseNumberSet(mixed $value): array
    {
        if (!\is_array($value)) {
            throw new \RuntimeException('Value is not an array: '.$value);
        }

        return [Field::TYPE_NS => \array_values(\array_map(fn ($v) => (string) $v, $value))];
    }

    /** @return array<Field::TYPE_SS, list<string>> */
    private function parseStringSet(mixed $value): array
    {
        if (!\is_array($value)) {
            throw new \RuntimeException('Value is not an array: '.$value);
        }

        return [Field::TYPE_SS => \array_values(\array_map(fn ($v) => (string) $v, $value))];
    }
}
