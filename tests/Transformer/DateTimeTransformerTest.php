<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Transformer;

use HarmonicDigital\DynamodbOdm\Test\Model\DateTimeObject;
use HarmonicDigital\DynamodbOdm\Transformer\DateTimeTransformer;
use HarmonicDigital\DynamodbOdm\Transformer\Exception\TransformationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DateTimeTransformer::class)]
#[CoversClass(TransformationException::class)]
#[UsesClass(DateTimeObject::class)]
class DateTimeTransformerTest extends TestCase
{
    private DateTimeTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new DateTimeTransformer();
    }

    public static function timestampProvider(): iterable
    {
        $dateTimeObject = new DateTimeObject();

        yield 'DateTime' => [
            '1609459200.654321',
            $dateTimeObject->dateTime,
            new \ReflectionProperty($dateTimeObject, 'dateTime'),
            \DateTime::class,
        ];

        yield 'DateTimeImmutable' => [
            '1609545600.000000',
            $dateTimeObject->dateTimeImmutable,
            new \ReflectionProperty($dateTimeObject, 'dateTimeImmutable'),
            \DateTimeImmutable::class,
        ];

        yield 'DateTimeInterface' => [
            '1609632000.000000',
            $dateTimeObject->dateTimeInterface,
            new \ReflectionProperty($dateTimeObject, 'dateTimeInterface'),
            \DateTimeImmutable::class,
        ];

        yield 'UndefinedDateTime' => [
            '1609632000.000000',
            $dateTimeObject->undefinedDateTime,
            new \ReflectionProperty($dateTimeObject, 'undefinedDateTime'),
            \DateTimeImmutable::class,
        ];
    }

    #[DataProvider('timestampProvider')]
    public function testTransformToDatabase(
        string $timestamp,
        \DateTimeInterface $dateTime,
        \ReflectionProperty $property,
    ): void {
        $this->assertSame($timestamp, $this->transformer->toDatabase($dateTime, $property));
    }

    #[DataProvider('timestampProvider')]
    public function testTransformFromDatabase(
        string $timestamp,
        \DateTimeInterface $dateTime,
        \ReflectionProperty $property,
        string $className,
    ): void {
        $result = $this->transformer->fromDatabase($timestamp, $property);
        $this->assertInstanceOf($className, $result);
        $this->assertEquals($dateTime, $result);
    }

    public function testErrorOnInvalidProperty(): void
    {
        $dateTimeObject = new DateTimeObject();
        $this->expectException(TransformationException::class);
        $this->expectExceptionMessage('Value must be an instance of \DateTimeInterface');
        $this->transformer->toDatabase($dateTimeObject->notDateTime, new \ReflectionProperty($dateTimeObject, 'notDateTime'));
    }
}
