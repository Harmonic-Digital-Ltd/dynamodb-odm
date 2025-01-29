<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Parser;

use Aws\DynamoDb\BinaryValue;
use Aws\DynamoDb\NumberValue;
use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Attribute\Key;
use HarmonicDigital\DynamodbOdm\Attribute\PartitionKey;
use HarmonicDigital\DynamodbOdm\Attribute\SortKey;
use HarmonicDigital\DynamodbOdm\Transformer\Transformer;

final readonly class MappedField
{
    public string $propertyName;
    public string $fieldName;

    private ?Transformer $transformer;
    private ?Key $key;

    public function __construct(
        public Field $field,
        public \ReflectionProperty $property,
        public MappedItem $mappedItem,
    ) {
        $this->propertyName = $property->getName();
        $this->fieldName = $this->field->name ?? $this->propertyName;
        $k = $property->getAttributes(PartitionKey::class)[0]
            ?? $property->getAttributes(SortKey::class)[0]
            ?? null;
        $this->key = $k?->newInstance();
        $t = null;
        foreach ($property->getAttributes() as $attribute) {
            $i = $attribute->newInstance();
            if ($i instanceof Transformer) {
                $t = $i;

                break;
            }
        }

        $this->transformer = $t;
    }

    public function transformToDatabaseValue(mixed $value): mixed
    {
        if (null !== $this->transformer) {
            $value = $this->transformer->toDatabase($value, $this->property);
        }

        $type = $this->getType($value);

        if (Field::TYPE_N === $type && \is_string($value)) {
            $value = new NumberValue($value);
        }

        if (Field::TYPE_B === $type && !$value instanceof BinaryValue) {
            $value = new BinaryValue($value);
        }

        if ($this->isPartitionKey() && null !== $this->mappedItem->item->partitionKeyPrefix) {
            $value = $this->mappedItem->item->partitionKeyPrefix.$value;
        }

        return $value;
    }

    public function transformFromDatabaseValue(mixed $value): mixed
    {
        if ($this->isPartitionKey() && null !== $this->mappedItem->item->partitionKeyPrefix) {
            $value = \str_replace($this->mappedItem->item->partitionKeyPrefix, '', $value);
        }

        if (null === $this->transformer) {
            return $value;
        }

        return $this->transformer->fromDatabase($value, $this->property);
    }

    /**
     * @return null|Field::TYPE_*
     */
    public function getType(mixed $value = null): ?string
    {
        if (null !== $this->field->type) {
            return $this->field->type;
        }

        if (null !== $value) {
            $result = self::inferType($value);
            if (null !== $result) {
                return $result;
            }
        }

        $type = $this->property->getType();

        if (!$type instanceof \ReflectionNamedType) {
            return null;
        }

        return match ($type->getName()) {
            'string' => Field::TYPE_S,
            'int', 'float', NumberValue::class => Field::TYPE_N,
            BinaryValue::class => Field::TYPE_B,
            'bool' => Field::TYPE_BOOL,
            default => null,
        };
    }

    public static function inferType(mixed $value): ?string
    {
        if (null === $value) {
            return Field::TYPE_NULL;
        }

        if ($value instanceof BinaryValue) {
            return Field::TYPE_B;
        }

        if (\is_bool($value)) {
            return Field::TYPE_BOOL;
        }

        if (\is_array($value)) {
            if (!\array_is_list($value)) {
                return Field::TYPE_M;
            }

            $type = null;
            foreach ($value as $v) {
                if (null === $type) {
                    $type = self::inferType($v);
                } elseif ($type !== self::inferType($v)) {
                    return Field::TYPE_L;
                }
            }

            return match ($type) {
                Field::TYPE_S => Field::TYPE_SS,
                Field::TYPE_N => Field::TYPE_NS,
                Field::TYPE_B => Field::TYPE_BS,
                default => null,
            };
        }

        if (\is_string($value)) {
            return Field::TYPE_S;
        }

        if (\is_int($value) || \is_float($value)) {
            return Field::TYPE_N;
        }

        return null;
    }

    public function isPartitionKey(): bool
    {
        return $this->key instanceof PartitionKey;
    }

    public function isSortKey(): bool
    {
        return $this->key instanceof SortKey;
    }

    public function getKey(): ?Key
    {
        return $this->key;
    }
}
