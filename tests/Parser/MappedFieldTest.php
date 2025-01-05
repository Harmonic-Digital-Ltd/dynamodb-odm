<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Parser;

use Aws\DynamoDb\BinaryValue;
use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Parser\MappedField;
use HarmonicDigital\DynamodbOdm\Test\Model\TestObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MappedField::class)]
class MappedFieldTest extends TestCase
{
    public static function typeProvider(): iterable
    {
        yield 'string' => ['this is a string', 'S'];

        yield 'int' => [1, 'N'];

        yield 'float' => [1.1, 'N'];

        yield 'bool' => [true, 'BOOL'];

        yield 'null' => [null, 'NULL'];

        yield 'list' => [[1, 'two', 3.5, new BinaryValue('value')], 'L'];

        yield 'map' => [['key' => 'value', 'hello' => 'world'], 'M'];

        yield 'string set' => [['one', 'two', 'three'], 'SS'];

        yield 'number set' => [[1, 2.5, -3], 'NS'];

        yield 'binary set' => [[new BinaryValue('binary'), new BinaryValue('encoded')], 'BS'];
    }

    #[DataProvider('typeProvider')]
    public function testInferType(mixed $data, string $expected): void
    {
        $this->assertSame($expected, MappedField::inferType($data));
    }

    public function testConstructor(): void
    {
        $field = self::generateField('id');
        $this->assertTrue($field->isPartitionKey());
        $this->assertFalse($field->isSortKey());
        $this->assertSame('id', $field->fieldName);
        $this->assertSame('S', $field->getType());
        $this->assertSame('S', $field->getType('foo'));
        $this->assertSame('HASH', $field->getKey()->keyType);
    }

    public function testSortKey(): void
    {
        $field = self::generateField('age');
        $this->assertFalse($field->isPartitionKey());
        $this->assertTrue($field->isSortKey());
        $this->assertSame('age', $field->fieldName);
        $this->assertSame('N', $field->getType());
        $this->assertSame('N', $field->getType(2));
        $this->assertSame('RANGE', $field->getKey()->keyType);
    }

    public function testComplex(): void
    {
        $field = self::generateField('map');
        $this->assertFalse($field->isPartitionKey());
        $this->assertFalse($field->isSortKey());
        $this->assertSame('map', $field->fieldName);
        $this->assertNull($field->getType());
        $this->assertSame('M', $field->getType(['foo' => 'bar']));
        $this->assertNull($field->getKey());
    }

    private static function generateField(string $property): MappedField
    {
        $reflectionProperty = new \ReflectionProperty(TestObject::class, $property);

        return new MappedField(
            $reflectionProperty->getAttributes(Field::class)[0]->newInstance(),
            $reflectionProperty,
        );
    }
}
