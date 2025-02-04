<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Transformer;

use Aws\DynamoDb\NumberValue;
use HarmonicDigital\DynamodbOdm\Transformer\Exception\TransformationException;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class DateTimeTransformer implements Transformer
{
    public function __construct(
        public string $format = 'U.u'
    ) {}

    public function toDatabase(mixed $value, \ReflectionProperty $property): NumberValue|string
    {
        if (!$value instanceof \DateTimeInterface) {
            throw new TransformationException('Value must be an instance of \DateTimeInterface');
        }

        $dbValue = $value->format($this->format);

        return \is_numeric($dbValue) ? new NumberValue($dbValue) : $dbValue;
    }

    public function fromDatabase(null|array|bool|float|int|object|string $value, \ReflectionProperty $property): \DateTimeInterface
    {
        $type = $property->getType();
        $declaredType = null;

        if ($type instanceof \ReflectionNamedType) {
            $declaredType = $type->getName();
        }

        $class = \DateTime::class === $declaredType ? \DateTime::class : \DateTimeImmutable::class;

        if (is_int($value)) {
            return $class::createFromFormat('U', (string) $value);
        }

        return $class::createFromFormat($this->format, (string) $value);
    }
}
