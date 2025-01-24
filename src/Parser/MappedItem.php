<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Parser;

use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Attribute\Item;

/**
 * @internal
 *
 * @template T of object
 */
final readonly class MappedItem
{
    public Item $item;
    private \ReflectionClass $class;

    /** @var array<string, MappedField> */
    private array $fields;
    private MappedField $partitionKeyProperty;
    private ?MappedField $sortKeyProperty;

    /** @param class-string<T> $className */
    public function __construct(
        public string $className,
        private FieldParserInterface $fieldParser = new FieldParser(),
        private ?string $tableName = null,
    ) {
        $this->class = new \ReflectionClass($className);
        $item = $this->class->getAttributes(Item::class)[0] ?? null;
        if (null === $item) {
            throw new \InvalidArgumentException('Not a DynamoDb item');
        }

        $this->item = $item->newInstance();

        $properties = $this->class->getProperties();

        $itemFields = [];
        $pk = null;
        $sk = null;

        foreach ($properties as $property) {
            $fieldAttribute = $property->getAttributes(Field::class)[0] ?? null;
            if (null === $fieldAttribute) {
                continue;
            }

            /** @var Field $type */
            $type = $fieldAttribute->newInstance();

            $mf = new MappedField(
                $type,
                $property,
                $this,
            );
            $itemFields[$mf->propertyName] = $mf;
            if ($mf->isPartitionKey()) {
                $pk = $mf;
            } elseif ($mf->isSortKey()) {
                $sk = $mf;
            }
        }

        $this->fields = $itemFields;

        if (null === $pk) {
            throw new \InvalidArgumentException('Partition key not found');
        }

        $this->partitionKeyProperty = $pk;
        $this->sortKeyProperty = $sk;
    }

    public function getTableName(): string
    {
        return $this->tableName
            ?? $this->item->tableName
            ?? $this->class->getShortName();
    }

    /**
     * @return array<string, MappedField>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getPartitionKey(): MappedField
    {
        return $this->partitionKeyProperty;
    }

    public function getSortKey(): ?MappedField
    {
        return $this->sortKeyProperty;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getFieldValues(object $object): array
    {
        $itemFields = [];

        foreach ($this->fields as $field) {
            $itemFields[$field->fieldName] = $this->fieldParser->toDynamoDb(
                $field,
                $field->property->getValue($object)
            );
        }

        return $itemFields;
    }

    /**
     * @return array<string, mixed>
     */
    public function getKeyFieldsValues(object $object): array
    {
        $pk = $this->getPartitionKey();
        $key = [
            $pk->fieldName => $this->fieldParser->toDynamoDb(
                $pk,
                $pk->property->getValue($object)
            ),
        ];

        $sk = $this->getSortKey();

        if (null !== $sk) {
            $key[$sk->fieldName] = $this->fieldParser->toDynamoDb(
                $sk,
                $sk->property->getValue($object)
            );
        }

        return $key;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function generateKeyFieldQuery(mixed $pk, mixed $sk = null): array
    {
        $pkField = $this->getPartitionKey();
        $key = [
            $pkField->fieldName => $this->fieldParser->toDynamoDb(
                $pkField,
                $pk
            ),
        ];

        $skField = $this->getSortKey();

        if (null !== $skField) {
            $key[$skField->fieldName] = $this->fieldParser->toDynamoDb(
                $skField,
                $sk
            );
        }

        return $key;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCreateTableParams(): array
    {
        $params = [
            'TableName' => $this->getTableName(),
            'KeySchema' => [],
            'AttributeDefinitions' => [],
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => $this->item->readCapacityUnits,
                'WriteCapacityUnits' => $this->item->writeCapacityUnits,
            ],
        ];

        foreach ($this->fields as $field) {
            if (null === $key = $field->getKey()) {
                continue;
            }
            $params['KeySchema'][] = [
                'AttributeName' => $field->fieldName,
                'KeyType' => $key->keyType,
            ];
            $params['AttributeDefinitions'][] = [
                'AttributeName' => $field->fieldName,
                'AttributeType' => $field->getType(),
            ];
        }

        return $params;
    }
}
