<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Parser;

use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Attribute\PartitionKey;
use HarmonicDigital\DynamodbOdm\Attribute\SortKey;
use HarmonicDigital\DynamodbOdm\Transformer\Transformer;

readonly class MappedField
{
    public bool $isPartitionKey;
    public bool $isSortKey;

    private ?Transformer $transformer;

    public function __construct(
        public Field $field,
        public string $fieldName,
        public string $propertyName,
        public \ReflectionProperty $property,
    ) {
        $pkAttribute = $property->getAttributes(PartitionKey::class)[0] ?? null;
        $this->isPartitionKey = null !== $pkAttribute;
        $skAttribute = $property->getAttributes(SortKey::class)[0] ?? null;
        $this->isSortKey = null !== $skAttribute;
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
        if (null === $this->transformer) {
            return $value;
        }

        return $this->transformer->toDatabase($value, $this->property);
    }

    public function transformFromDatabaseValue(mixed $value): mixed
    {
        if (null === $this->transformer) {
            return $value;
        }

        return $this->transformer->fromDatabase($value, $this->property);
    }
}
