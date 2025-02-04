<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Parser;

use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Attribute\Item;
use HarmonicDigital\DynamodbOdm\Attribute\Key;
use HarmonicDigital\DynamodbOdm\Attribute\PartitionKey;
use HarmonicDigital\DynamodbOdm\Attribute\SortKey;
use HarmonicDigital\DynamodbOdm\Parser\FieldParser;
use HarmonicDigital\DynamodbOdm\Parser\MappedField;
use HarmonicDigital\DynamodbOdm\Parser\MappedItem;
use HarmonicDigital\DynamodbOdm\Test\Model\EmbeddedItem;
use HarmonicDigital\DynamodbOdm\Test\Model\TestEmbeddedObject;
use HarmonicDigital\DynamodbOdm\Test\Model\TestObject;
use HarmonicDigital\DynamodbOdm\Transformer\DateTimeTransformer;
use HarmonicDigital\DynamodbOdm\Transformer\Normalizer\MapableNormalizer;
use HarmonicDigital\DynamodbOdm\Transformer\Normalizer\TransformedNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FieldParser::class)]
#[UsesClass(MappedItem::class)]
#[UsesClass(TestObject::class)]
#[UsesClass(TestEmbeddedObject::class)]
#[UsesClass(EmbeddedItem::class)]
#[UsesClass(Field::class)]
#[UsesClass(Item::class)]
#[UsesClass(Key::class)]
#[UsesClass(PartitionKey::class)]
#[UsesClass(SortKey::class)]
#[UsesClass(MappedField::class)]
#[UsesClass(DateTimeTransformer::class)]
#[UsesClass(MapableNormalizer::class)]
class FieldParserTest extends TestCase
{
    private FieldParser $fieldParser;

    protected function setUp(): void
    {
        $this->fieldParser = new FieldParser();
    }

    #[DataProvider('objectDataProvider')]
    public function testId(object $object, array $expected)
    {
        $mappedItem = new MappedItem($object::class, $this->fieldParser);
        $fields = $mappedItem->getFieldValues($object);
        $this->assertSame($expected, $fields);
    }

    public static function objectDataProvider(): iterable
    {
        yield 'Standard Test object' => [
            new TestObject(),
            [
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
                'list' => [
                    'L' => [['S' => 'one'], ['N' => '2'], ['BOOL' => true], ['N' => '4.5']],
                ],
                'stringSet' => ['SS' => ['one', 'two', 'three']],
                'numberSet' => ['NS' => ['1', '2.5', '-3']],
                'binarySet' => ['BS' => ['binary1', 'binary2']],
                'dateTimeImmutable' => ['N' => '1609459200.000000'],
            ],
        ];

        yield 'embedded object' => [
            new TestEmbeddedObject('id', new EmbeddedItem('name', 30)),
            [
                'id' => ['S' => 'id'],
                'embeddedItem' => ['M' => ['name' => ['S' => 'name'], 'value' => ['N' => '30']]],
            ],
        ];
    }
}
