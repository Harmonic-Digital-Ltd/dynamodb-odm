<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Parser;

use Aws\DynamoDb\Marshaler;
use HarmonicDigital\DynamodbOdm\Attribute\Field;

class FieldParser
{
    public function __construct(
        private Marshaler $marshaller = new Marshaler(),
    ) {}

    /** @return array<Field::TYPE_*, mixed> */
    public function toDynamoDb(?MappedField $field, mixed $value): array
    {
        $value = $field?->transformToDatabaseValue($value) ?? $value;

        return match ($field?->field->type) {
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
        $marshaller = new Marshaler();

        /** @var array<string, mixed> $value */
        foreach ($item as $key => $value) {
            $v = $marshaller->unmarshalValue($value);
            $v = $fields[$key]->transformFromDatabaseValue($v);
            $result[$fields[$key]->propertyName] = $v;
        }

        return $result;
    }

    /** @return array<Field::TYPE_BS, list<string>> */
    private function parseBinarySet(mixed $value): array
    {
        if (!\is_array($value)) {
            throw new \RuntimeException('Value is not an array: '.$value);
        }

        return [Field::TYPE_BS => \array_values(\array_map(fn ($v) => \base64_encode($v), $value))];
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
