<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Transformer\Normalizer;

use HarmonicDigital\DynamodbOdm\Transformer\Normalizer\PrenormalizedValue;
use HarmonicDigital\DynamodbOdm\Transformer\Normalizer\TransformedNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\LogicException;

/**
 * @internal
 */
#[CoversClass(TransformedNormalizer::class)]
#[CoversClass(PrenormalizedValue::class)]
class TransformedNormalizerTest extends TestCase
{
    private TransformedNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new TransformedNormalizer();
    }

    public function testDenormalizeWithValidData(): void
    {
        $value = new PrenormalizedValue('testValue');

        $result = $this->normalizer->denormalize($value, 'string');

        $this->assertSame('testValue', $result);
    }

    public function testDenormalizeWithInvalidDataThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The data must be an instance of PrenormalizedValue');

        $this->normalizer->denormalize('invalidData', 'string');
    }

    public function testSupportsDenormalizationWithPrenormalizedValue(): void
    {
        $value = new PrenormalizedValue('testValue');

        $this->assertTrue($this->normalizer->supportsDenormalization($value, 'string'));
    }

    public function testSupportsDenormalizationWithInvalidData(): void
    {
        $this->assertFalse($this->normalizer->supportsDenormalization('invalidData', 'string'));
    }

    public function testGetSupportedTypes(): void
    {
        $this->assertSame(['*' => true], $this->normalizer->getSupportedTypes(null));
    }
}
