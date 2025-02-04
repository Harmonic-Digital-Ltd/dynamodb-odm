<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Transformer\Normalizer;

use HarmonicDigital\DynamodbOdm\Test\Model\EmbeddedItem;
use HarmonicDigital\DynamodbOdm\Test\Model\TestEmbeddedObject;
use HarmonicDigital\DynamodbOdm\Test\Model\TestMultipleEmbeddedObject;
use HarmonicDigital\DynamodbOdm\Transformer\Normalizer\MapableNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MapableNormalizer::class)]
class MapableNormalizerTest extends TestCase
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
    public function testNormalize(object $object, array $databaseData, string $className, string $property)
    {
        $transformer = new MapableNormalizer();
        $result = $transformer->normalize($object, $object::class);
        $this->assertSame($databaseData, $result);
    }

    #[DataProvider('itemProvider')]
    public function testDenormalize(object $object, array $databaseData, string $className, string $property)
    {
        $transformer = new MapableNormalizer();
        $result = $transformer->denormalize($databaseData, $object::class);
        $this->assertInstanceOf($object::class, $result);
        $this->assertEquals($object, $result);
    }
}
