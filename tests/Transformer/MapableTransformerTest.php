<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Transformer;

use HarmonicDigital\DynamodbOdm\Test\Model\EmbeddedItem;
use HarmonicDigital\DynamodbOdm\Test\Model\TestEmbeddedObject;
use HarmonicDigital\DynamodbOdm\Test\Model\TestMultipleEmbeddedObject;
use HarmonicDigital\DynamodbOdm\Transformer\MapableTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MapableTransformer::class)]
class MapableTransformerTest extends TestCase
{
    public static function itemProvider(): iterable
    {
        yield 'EmbeddedItem' => [
            new EmbeddedItem('test', 123),
            ['name' => 'test', 'value' => 123],
            TestEmbeddedObject::class,
            'embeddedItem',
        ];
        $data = new TestMultipleEmbeddedObject('id');

        yield 'EmbeddedItemCollection' => [
            $data->embeddedItems,
            [
                'items' => [
                    [
                        'name' => 'First',
                        'value' => 1,
                    ],
                    [
                        'name' => 'Second',
                        'value' => 2,
                    ],
                ],
            ],
            $data::class,
            'embeddedItems',
        ];
    }

    #[DataProvider('itemProvider')]
    public function testToDatabase(mixed $object, array $databaseData, string $className, string $property)
    {
        $transformer = new MapableTransformer();
        $result = $transformer->toDatabase($object, new \ReflectionProperty($className, $property));
        $this->assertSame($databaseData, $result);
    }

    #[DataProvider('itemProvider')]
    public function testFromDatabase(mixed $object, array $databaseData, string $className, string $property)
    {
        $transformer = new MapableTransformer();
        $result = $transformer->fromDatabase($databaseData, new \ReflectionProperty($className, $property));
        $this->assertInstanceOf($object::class, $result);
        $this->assertEquals($object, $result);
    }
}
