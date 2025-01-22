<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Model;

use Aws\DynamoDb\BinaryValue;
use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Attribute\Item;
use HarmonicDigital\DynamodbOdm\Attribute\PartitionKey;
use HarmonicDigital\DynamodbOdm\Attribute\SortKey;
use HarmonicDigital\DynamodbOdm\Transformer\DateTimeTransformer;

#[Item('test', partitionKeyPrefix: 'PREFIX#')]
class TestObject
{
    #[Field]
    #[PartitionKey]
    private string $id;
    #[Field]
    private string $name;
    #[Field]
    #[SortKey]
    private int $age;

    #[Field]
    private float $float = 3.14;

    #[Field]
    private float $intAsFloat = 4;

    #[Field(type: 'N')]
    private string $numericString = '123.456789';

    #[Field]
    private ?string $nullable = null;

    #[Field]
    private bool $bool = true;

    #[Field]
    private BinaryValue $binary;

    #[Field(type: 'B')]
    private string $binaryString = 'binaryString';

    #[Field]
    private array $map = ['key' => 'value', 'hello' => 'world'];

    #[Field]
    private array $list = ['one', 2, true, 4.5];

    #[Field('SS')]
    private array $stringSet = ['one', 'two', 'three'];

    #[Field('NS')]
    private array $numberSet = [1, 2.5, -3];

    #[Field('BS')]
    private array $binarySet;

    #[Field]
    #[DateTimeTransformer]
    private \DateTimeImmutable $dateTimeImmutable;

    private string $unmapped;

    public function __construct(string $id = 'id', string $name = 'name', int $age = 30, string $unmapped = 'unmapped')
    {
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
        $this->binary = new BinaryValue('binary');
        $this->binarySet = [new BinaryValue('binary1'), new BinaryValue('binary2')];
        $this->unmapped = $unmapped;
        $this->dateTimeImmutable = new \DateTimeImmutable('2021-01-01T00:00:00.000000Z');
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getUnmapped(): string
    {
        return $this->unmapped;
    }

    public function getNullable(): ?string
    {
        return $this->nullable;
    }

    public function isBool(): bool
    {
        return $this->bool;
    }

    public function getMap(): array
    {
        return $this->map;
    }

    public function getList(): array
    {
        return $this->list;
    }

    public function getStringSet(): array
    {
        return $this->stringSet;
    }

    public function getNumberSet(): array
    {
        return $this->numberSet;
    }

    public function getFloat(): float
    {
        return $this->float;
    }

    public function getIntAsFloat(): float
    {
        return $this->intAsFloat;
    }

    public function getDateTimeImmutable(): \DateTimeImmutable
    {
        return $this->dateTimeImmutable;
    }

    public function getBinary(): BinaryValue
    {
        return $this->binary;
    }

    public function getBinarySet(): array
    {
        return $this->binarySet;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getBinaryString(): string
    {
        return $this->binaryString;
    }

    public function getNumericString(): string
    {
        return $this->numericString;
    }
}
