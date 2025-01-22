<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Transformer;

use HarmonicDigital\DynamodbOdm\Test\Model\EmbeddedItem;
use HarmonicDigital\DynamodbOdm\Test\Model\TestEmbeddedObject;
use HarmonicDigital\DynamodbOdm\Transformer\MapableTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MapableTransformer::class)]
class MapableTransformerTest extends TestCase
{
    public function testToDatabase()
    {
        $transformer = new MapableTransformer();
        $expected = [
            'name' => 'test',
            'value' => 123,
        ];
        $data = new EmbeddedItem('test', 123);
        $result = $transformer->toDatabase($data, new \ReflectionProperty(TestEmbeddedObject::class, 'embeddedItem'));
        $this->assertSame($expected, $result);
    }

    public function testFromDatabase()
    {
        $transformer = new MapableTransformer();
        $data = [
            'name' => 'test',
            'value' => 123,
        ];
        $result = $transformer->fromDatabase($data, new \ReflectionProperty(TestEmbeddedObject::class, 'embeddedItem'));
        $this->assertInstanceOf(EmbeddedItem::class, $result);
        $this->assertSame('test', $result->name);
        $this->assertSame(123, $result->value);
    }
}
