<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Model;

use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Attribute\Item;
use HarmonicDigital\DynamodbOdm\Attribute\PartitionKey;
use HarmonicDigital\DynamodbOdm\Attribute\SortKey;

#[Item(writeCapacityUnits: 10)]
class TestObjectTwo
{
    #[Field('S')]
    #[PartitionKey]
    private string $id;
    #[Field('S')]
    private string $name;
    #[Field('N')]
    #[SortKey]
    private int $age;

    #[Field('N')]
    private float $float = 3.14;

    #[Field('N')]
    private float $intAsFloat = 4;

    #[Field('S')]
    private ?string $nullable = null;

    #[Field('B')]
    private string $binary = 'binary';

    #[Field('BOOL')]
    private bool $bool = true;

    #[Field('M')]
    private array $map = ['key' => 'value', 'hello' => 'world'];

    #[Field('L')]
    private array $list = ['one', 2, true, 4.5];

    #[Field('SS')]
    private array $stringSet = ['one', 'two', 'three'];

    #[Field('NS')]
    private array $numberSet = [1, 2.5, -3];

    #[Field('BS')]
    private array $binarySet = ['binary', 'encoded'];

    private string $unmapped;

    public function __construct(string $id = 'id', string $name = 'name', int $age = 30, string $unmapped = 'unmapped')
    {
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
        $this->unmapped = $unmapped;
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

    public function getBinary(): string
    {
        return $this->binary;
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

    public function getBinarySet(): array
    {
        return $this->binarySet;
    }

    public function getFloat(): float
    {
        return $this->float;
    }

    public function getIntAsFloat(): float
    {
        return $this->intAsFloat;
    }
}
