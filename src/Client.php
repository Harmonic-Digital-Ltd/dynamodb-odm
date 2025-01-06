<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm;

use Aws\DynamoDb\DynamoDbClient;
use HarmonicDigital\DynamodbOdm\Parser\FieldParser;
use HarmonicDigital\DynamodbOdm\Parser\MappedItem;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Client
{
    /** @var array<class-string, MappedItem> */
    private array $mappedItems = [];
    private ObjectNormalizer $normalizer;

    public function __construct(
        private DynamoDbClient $dynamoDbClient,
        private FieldParser $fieldParser = new FieldParser(),
    ) {
        $this->normalizer = new ObjectNormalizer();
    }

    public function put(object $object): void
    {
        $mappedItem = $this->getMappedItem($object::class);

        $this->dynamoDbClient->putItem([
            'TableName' => $mappedItem->getTableName(),
            'Item' => $mappedItem->getFieldValues($object, $this->fieldParser),
        ]);
    }

    public function delete(object $item): void
    {
        $mappedItem = $this->getMappedItem($item::class);

        $this->dynamoDbClient->deleteItem([
            'TableName' => $mappedItem->getTableName(),
            'Key' => $mappedItem->getKeyFieldsValues($item, $this->fieldParser),
        ]);
    }

    /**
     * @template T as object
     *
     * @param class-string<T> $className
     *
     * @return null|T
     */
    public function getItem(
        string $className,
        mixed $partitionKey,
        mixed $sortKey = null
    ): ?object {
        $mappedItem = $this->getMappedItem($className);

        $result = $this->dynamoDbClient->getItem([
            'TableName' => $mappedItem->getTableName(),
            'Key' => $mappedItem->generateKeyFieldQuery($this->fieldParser, $partitionKey, $sortKey),
        ]);

        if (!isset($result['Item'])) {
            return null;
        }

        return $this->parseItem($result['Item'], $mappedItem);
    }

    public function createTable(string $className): void
    {
        $mappedItem = $this->getMappedItem($className);

        $this->dynamoDbClient->createTable($mappedItem->getCreateTableParams());
    }

    /**
     * @template T as object
     *
     * @param MappedItem<T> $mappedItem
     *
     * @return T
     */
    private function parseItem(array $item, MappedItem $mappedItem): object
    {
        return $this->normalizer->denormalize(
            $this->fieldParser->dynamoDbToPropertyArray($item, $mappedItem),
            $mappedItem->className
        );
    }

    /** @param class-string $class */
    private function getMappedItem(string $class): MappedItem
    {
        return $this->mappedItems[$class] ??= new MappedItem($class);
    }
}
