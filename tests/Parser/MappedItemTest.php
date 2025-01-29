<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Parser;

use HarmonicDigital\DynamodbOdm\Parser\FieldParser;
use HarmonicDigital\DynamodbOdm\Parser\MappedItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MappedItem::class)]
#[UsesClass(FieldParser::class)]
class MappedItemTest extends TestCase
{
    public function testNotMappedItem(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Not a DynamoDb item');
        new MappedItem(\stdClass::class);
    }
}
