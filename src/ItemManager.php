<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm;

use Aws\DynamoDb\DynamoDbClient;
use HarmonicDigital\DynamodbOdm\Parser\FieldParser;
use HarmonicDigital\DynamodbOdm\Parser\FieldParserInterface;
use HarmonicDigital\DynamodbOdm\Parser\MappedItem;
use HarmonicDigital\DynamodbOdm\Transformer\MapableTransformer;
use Symfony\Component\Serializer\Serializer;

final class ItemManager implements ItemManagerInterface
{
    /** @var array<class-string, MappedItem> */
    private array $mappedItems = [];
    private readonly Serializer $serializer;

    /** @param array<class-string, string> $tableMap item to table mapper */
    public function __construct(
        private readonly DynamoDbClient $dynamoDbClient,
        private array $tableMap = [],
        private readonly FieldParserInterface $fieldParser = new FieldParser(),
    ) {
        $this->serializer = new Serializer([new MapableTransformer(), $this->fieldParser->getNormalizer()]);
    }

    public function put(object $object): void
    {
        $mappedItem = $this->getMappedItem($object::class);

        $this->dynamoDbClient->putItem([
            'TableName' => $mappedItem->getTableName(),
            'Item' => $mappedItem->getFieldValues($object),
        ]);
    }

    public function delete(object $item): void
    {
        $mappedItem = $this->getMappedItem($item::class);

        $this->dynamoDbClient->deleteItem([
            'TableName' => $mappedItem->getTableName(),
            'Key' => $mappedItem->getKeyFieldsValues($item),
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
            'Key' => $mappedItem->generateKeyFieldQuery($partitionKey, $sortKey),
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
     * @param class-string $item  The item
     * @param string       $table The table to store the item in
     */
    public function setTable(string $item, string $table): void
    {
        $this->tableMap[$item] = $table;
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
        return $this->serializer->denormalize(
            $this->fieldParser->dynamoDbToPropertyArray($item, $mappedItem),
            $mappedItem->className
        );
    }

    /**
     * @template T as object
     *
     * @param class-string<T> $class
     *
     * @return MappedItem<T>
     */
    private function getMappedItem(string $class): MappedItem
    {
        return $this->mappedItems[$class] ??= new MappedItem(
            $class,
            $this->fieldParser,
            $this->tableMap[$class] ?? null
        );
    }
}
