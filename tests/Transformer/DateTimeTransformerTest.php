<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Transformer;

use Aws\DynamoDb\NumberValue;
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
    public static function timestampProvider(): iterable
    {
        $dateTimeObject = new DateTimeObject();
        $defaultTransformer = new DateTimeTransformer();

        yield 'DateTime' => [
            $defaultTransformer,
            new NumberValue('1609459200.654321'),
            $dateTimeObject->dateTime,
            new \ReflectionProperty($dateTimeObject, 'dateTime'),
            \DateTime::class,
        ];

        yield 'DateTimeImmutable' => [
            $defaultTransformer,
            new NumberValue('1609545600.000000'),
            $dateTimeObject->dateTimeImmutable,
            new \ReflectionProperty($dateTimeObject, 'dateTimeImmutable'),
            \DateTimeImmutable::class,
        ];

        yield 'DateTimeInterface' => [
            $defaultTransformer,
            new NumberValue('1609632000.000000'),
            $dateTimeObject->dateTimeInterface,
            new \ReflectionProperty($dateTimeObject, 'dateTimeInterface'),
            \DateTimeImmutable::class,
        ];

        yield 'UndefinedDateTime' => [
            $defaultTransformer,
            new NumberValue('1609632000.000000'),
            $dateTimeObject->undefinedDateTime,
            new \ReflectionProperty($dateTimeObject, 'undefinedDateTime'),
            \DateTimeImmutable::class,
        ];

        $customTransformer = new DateTimeTransformer('Y-m-d\TH:i:s.uP');

        yield 'Custom DateTime' => [
            $customTransformer,
            '2021-01-01T00:00:00.654321+00:00',
            $dateTimeObject->dateTime,
            new \ReflectionProperty($dateTimeObject, 'dateTime'),
            \DateTime::class,
        ];

        yield 'Custom DateTimeImmutable' => [
            $customTransformer,
            '2021-01-02T00:00:00.000000+00:00',
            $dateTimeObject->dateTimeImmutable,
            new \ReflectionProperty($dateTimeObject, 'dateTimeImmutable'),
            \DateTimeImmutable::class,
        ];
    }

    #[DataProvider('timestampProvider')]
    public function testTransformToDatabase(
        DateTimeTransformer $transformer,
        $timestamp,
        \DateTimeInterface $dateTime,
        \ReflectionProperty $property,
    ): void {
        $this->assertEquals($timestamp, $transformer->toDatabase($dateTime, $property));
    }

    #[DataProvider('timestampProvider')]
    public function testTransformFromDatabase(
        DateTimeTransformer $transformer,
        $timestamp,
        \DateTimeInterface $dateTime,
        \ReflectionProperty $property,
        string $className,
    ): void {
        $result = $transformer->fromDatabase($timestamp, $property);
        $this->assertInstanceOf($className, $result);
        $this->assertEquals($dateTime, $result);
    }

    public function testErrorOnInvalidProperty(): void
    {
        $transformer = new DateTimeTransformer();
        $dateTimeObject = new DateTimeObject();
        $this->expectException(TransformationException::class);
        $this->expectExceptionMessage('Value must be an instance of \DateTimeInterface');
        $transformer->toDatabase($dateTimeObject->notDateTime, new \ReflectionProperty($dateTimeObject, 'notDateTime'));
    }
}
