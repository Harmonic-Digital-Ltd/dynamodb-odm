<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test;

use Aws\DynamoDb\BinaryValue;
use Aws\DynamoDb\DynamoDbClient;
use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Attribute\Item;
use HarmonicDigital\DynamodbOdm\Attribute\Key;
use HarmonicDigital\DynamodbOdm\Attribute\PartitionKey;
use HarmonicDigital\DynamodbOdm\Attribute\SortKey;
use HarmonicDigital\DynamodbOdm\ItemManager;
use HarmonicDigital\DynamodbOdm\Parser\FieldParser;
use HarmonicDigital\DynamodbOdm\Parser\MappedField;
use HarmonicDigital\DynamodbOdm\Parser\MappedItem;
use HarmonicDigital\DynamodbOdm\Test\Model\EmbeddedItem;
use HarmonicDigital\DynamodbOdm\Test\Model\TestEmbeddedObject;
use HarmonicDigital\DynamodbOdm\Test\Model\TestObject;
use HarmonicDigital\DynamodbOdm\Test\Model\TestObjectTwo;
use HarmonicDigital\DynamodbOdm\Transformer\DateTimeTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ItemManager::class)]
#[CoversClass(FieldParser::class)]
#[CoversClass(MappedItem::class)]
#[CoversClass(MappedField::class)]
#[UsesClass(DateTimeTransformer::class)]
#[UsesClass(TestObject::class)]
#[UsesClass(Field::class)]
#[UsesClass(Item::class)]
#[UsesClass(Key::class)]
#[UsesClass(PartitionKey::class)]
#[UsesClass(SortKey::class)]
class ItemManagerTest extends TestCase
{
    private ItemManager $client;
    private DynamoDbClient&MockObject $dynamoDbClient;

    protected function setUp(): void
    {
        $this->dynamoDbClient = $this->createMock(DynamoDbClient::class);
        $this->client = new ItemManager($this->dynamoDbClient);
    }

    public function testPut(): void
    {
        $item = new TestObject();
        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with(
                'putItem',
                [
                    [
                        'TableName' => 'test',
                        'Item' => [
                            'id' => ['S' => 'PREFIX#id'],
                            'name' => ['S' => 'name'],
                            'age' => ['N' => '30'],
                            'float' => ['N' => '3.14'],
                            'intAsFloat' => ['N' => '4'],
                            'numericString' => ['N' => '123.456789'],
                            'binary' => ['B' => 'binary'],
                            'binaryString' => ['B' => 'binaryString'],
                            'nullable' => ['NULL' => true],
                            'bool' => ['BOOL' => true],
                            'map' => ['M' => ['key' => ['S' => 'value'], 'hello' => ['S' => 'world']]],
                            'list' => [
                                'L' => [['S' => 'one'], ['N' => '2'], ['BOOL' => true], ['N' => '4.5']],
                            ],
                            'stringSet' => ['SS' => ['one', 'two', 'three']],
                            'numberSet' => ['NS' => ['1', '2.5', '-3']],
                            'binarySet' => ['BS' => ['binary1', 'binary2']],
                            'dateTimeImmutable' => ['S' => '1609459200.000000'],
                        ],
                    ],
                ]
            )
        ;

        $this->client->put($item);
    }

    public function testDelete(): void
    {
        $item = new TestObject();
        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with(
                'deleteItem',
                [
                    [
                        'TableName' => 'test',
                        'Key' => [
                            'id' => ['S' => 'PREFIX#id'],
                            'age' => ['N' => '30'],
                        ],
                    ],
                ]
            )
        ;

        $this->client->delete($item);
    }

    public function testGetItem(): void
    {
        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with(
                'getItem',
                [
                    [
                        'TableName' => 'test',
                        'Key' => [
                            'id' => ['S' => 'PREFIX#id'],
                            'age' => ['N' => '30'],
                        ],
                    ],
                ]
            )
            ->willReturn([
                'Item' => [
                    'id' => ['S' => 'PREFIX#id'],
                    'name' => ['S' => 'name'],
                    'age' => ['N' => '30'],
                    'float' => ['N' => '3.14'],
                    'intAsFloat' => ['N' => '4'],
                    'numericString' => ['N' => '123.456789'],
                    'nullable' => ['NULL' => true],
                    'bool' => ['BOOL' => true],
                    'binary' => ['B' => 'binary'],
                    'binaryString' => ['B' => 'binaryString'],
                    'map' => ['M' => ['key' => ['S' => 'value'], 'hello' => ['S' => 'world']]],
                    'list' => ['L' => [['S' => 'one'], ['N' => '2'], ['BOOL' => true], ['N' => '4.5']]],
                    'stringSet' => ['SS' => ['one', 'two', 'three']],
                    'numberSet' => ['NS' => ['1', '2.5', '-3']],
                    'binarySet' => ['BS' => ['binary1', 'binary2']],
                    'dateTimeImmutable' => ['S' => '1609545600.000000'],
                ],
            ])
        ;

        /** @var TestObject $result */
        $result = $this->client->getItem(TestObject::class, 'id', 30);
        $this->assertInstanceOf(TestObject::class, $result);
        $this->assertSame('id', $result->getId());
        $this->assertSame('name', $result->getName());
        $this->assertSame(30, $result->getAge());
        $this->assertSame(3.14, $result->getFloat());
        $this->assertSame(4.0, $result->getIntAsFloat());
        $this->assertSame('unmapped', $result->getUnmapped());
        $this->assertSame('binary', $result->getBinary()->__toString());
        $this->assertSame('binaryString', $result->getBinaryString());
        $this->assertEquals([new BinaryValue('binary1'), new BinaryValue('binary2')], $result->getBinarySet());
        $this->assertNull($result->getNullable());
        $this->assertTrue($result->isBool());
        $this->assertSame(['key' => 'value', 'hello' => 'world'], $result->getMap());
        $this->assertSame(4.0, $result->getIntAsFloat());
        $this->assertSame(3.14, $result->getFloat());
        $this->assertSame('123.456789', $result->getNumericString());
        $this->assertSame(['one', 2, true, 4.5], $result->getList());
        $this->assertSame(['one', 'two', 'three'], $result->getStringSet());
        $this->assertSame([1, 2.5, -3], $result->getNumberSet());
        $this->assertEquals(new \DateTimeImmutable('2021-01-01T00:00:00.000000Z'), $result->getDateTimeImmutable());
    }

    public function testPutItemWithEmbedded(): void
    {
        $item = new TestEmbeddedObject('id', new EmbeddedItem('name', 30));
        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with(
                'putItem',
                [
                    [
                        'TableName' => 'TestEmbeddedObject',
                        'Item' => [
                            'id' => ['S' => 'id'],
                            'embeddedItem' => ['M' => ['name' => ['S' => 'name'], 'value' => ['N' => '30']]],
                        ],
                    ],
                ],
            )
        ;

        $this->client->put($item);
    }

    public function testGetItemEmbedded(): void
    {
        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with(
                'getItem',
                [
                    [
                        'TableName' => 'TestEmbeddedObject',
                        'Key' => [
                            'id' => ['S' => 'id'],
                        ],
                    ],
                ]
            )
            ->willReturn([
                'Item' => [
                    'id' => ['S' => 'id'],
                    'embeddedItem' => ['M' => ['name' => ['S' => 'name'], 'value' => ['N' => '30']]],
                ],
            ])
        ;

        /** @var TestEmbeddedObject $result */
        $result = $this->client->getItem(TestEmbeddedObject::class, 'id');
        $this->assertInstanceOf(TestEmbeddedObject::class, $result);
        $this->assertSame('id', $result->id);
        $this->assertInstanceOf(EmbeddedItem::class, $result->embeddedItem);
        $this->assertSame('name', $result->embeddedItem->name);
        $this->assertSame(30, $result->embeddedItem->value);
    }

    public function testCreateTable(): void
    {
        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with(
                'createTable',
                [
                    [
                        'TableName' => 'test',
                        'KeySchema' => [
                            ['AttributeName' => 'id', 'KeyType' => 'HASH'],
                            ['AttributeName' => 'age', 'KeyType' => 'RANGE'],
                        ],
                        'AttributeDefinitions' => [
                            ['AttributeName' => 'id', 'AttributeType' => 'S'],
                            ['AttributeName' => 'age', 'AttributeType' => 'N'],
                        ],
                        'ProvisionedThroughput' => [
                            'ReadCapacityUnits' => 5,
                            'WriteCapacityUnits' => 5,
                        ],
                    ],
                ]
            )
        ;

        $this->client->createTable(TestObject::class);
    }

    public function testCreateTableNoSpecifiedTableName(): void
    {
        $this->dynamoDbClient->expects($this->once())
            ->method('__call')
            ->with(
                'createTable',
                [
                    [
                        'TableName' => 'TestObjectTwo',
                        'KeySchema' => [
                            ['AttributeName' => 'id', 'KeyType' => 'HASH'],
                            ['AttributeName' => 'age', 'KeyType' => 'RANGE'],
                        ],
                        'AttributeDefinitions' => [
                            ['AttributeName' => 'id', 'AttributeType' => 'S'],
                            ['AttributeName' => 'age', 'AttributeType' => 'N'],
                        ],
                        'ProvisionedThroughput' => [
                            'ReadCapacityUnits' => 5,
                            'WriteCapacityUnits' => 10,
                        ],
                    ],
                ]
            )
        ;

        $this->client->createTable(TestObjectTwo::class);
    }
}
