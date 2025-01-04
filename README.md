# README

## Overview

This project provides an Object-Document Mapper (ODM) for DynamoDB, allowing you to map PHP objects to DynamoDB items and vice versa.

## Installation

To install the package, use Composer:

```bash
composer require harmonicdigital/dynamodb-odm
```

## Usage

### Example Model

Here is an example of a model class `MyItem` that can be used with the ODM:

```php
<?php


use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Attribute\Item;
use HarmonicDigital\DynamodbOdm\Attribute\PartitionKey;
use HarmonicDigital\DynamodbOdm\Attribute\SortKey;

#[Item(tableName: 'my_table')]
class MyItem
{
    #[Field] // Type 'S'
    #[PartitionKey] // Use as the partition key
    private string $id;

    #[Field(name: 'full_name')] // Use a different name for the DynamoDB Field, type S implied
    private string $name;

    #[Field('N')] // Type 'N'
    #[SortKey] // Use as the sort key
    private int $age;
    
    #[Field(type: 'SS')] // Force a string set for a string list
    /** @var list<string> */
    private array $stringList;
    
    public function __construct(
        string $id = '',
        string $name = '',
        int $age = 0,
        array $stringList = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
        $this->stringList = $stringList;
    }
    ) {}

    // Getters and setters...
}
```

### Client Usage

To interact with DynamoDB, use the `Client` class:

```php
<?php

use Aws\DynamoDb\DynamoDbClient;
use HarmonicDigital\DynamodbOdm\Client;
use App\Test\Model\MyItem;

$dynamoDbClient = new DynamoDbClient([
    'region' => 'us-west-2',
    'version' => 'latest',
    'credentials' => [
        'key' => 'your-access-key-id',
        'secret' => 'your-secret-access-key',
    ],
]);

$client = new Client($dynamoDbClient);

// Create the table
$client->createTable(MyItem::class);

// Create a new item
$item = new MyItem('id', 'My Full Name', 30, ['string1', 'string2']);

$client->put($item);

// Retrieve an item
$result = $client->getItem(MyItem::class, 'id', 30);
if ($result !== null) {
    echo $result->getName(); // Output: name
}

// Delete an item
$client->delete($item);

// Create a table
$client->createTable(MyItem::class);
```

## Transformers

Transformers are used to convert property values between a proprietary format and a DynamoDB format. Here is an example of a `DateTimeTransformer`:

```php
<?php

namespace HarmonicDigital\DynamodbOdm\Transformer;

use HarmonicDigital\DynamodbOdm\Transformer\Exception\TransformationException;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DateTimeTransformer implements Transformer
{
    public function __construct(
        public string $format = 'U.u'
    ) {}

    public function toDatabase(mixed $value, \ReflectionProperty $property): string
    {
        if (!$value instanceof \DateTimeInterface) {
            throw new TransformationException('Value must be an instance of \DateTimeInterface');
        }

        return $value->format($this->format);
    }

    public function fromDatabase(null|array|bool|float|int|string $value, \ReflectionProperty $property): \DateTimeInterface
    {
        $type = $property->getType();
        $declaredType = null;

        if ($type instanceof \ReflectionNamedType) {
            $declaredType = $type->getName();
        }

        if (\DateTime::class === $declaredType) {
            return \DateTime::createFromFormat($this->format, $value);
        }

        return \DateTimeImmutable::createFromFormat($this->format, $value);
    }
}
```

Usage:
```php
<?php

use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Attribute\Item;
use HarmonicDigital\DynamodbOdm\Attribute\PartitionKey;
use HarmonicDigital\DynamodbOdm\Attribute\SortKey;
use HarmonicDigital\DynamodbOdm\Transformer\DateTimeTransformer;

#[Item(tableName: 'my_table')]
class MyItem
{
    #[Field] // Type 'S'
    #[PartitionKey] // Use as the partition key
    private string $id;

    #[Field(name: 'full_name')] // Use a different name for the DynamoDB Field
    private string $name;

    #[Field] // Type 'N'
    #[SortKey] // Use as the sort key
    private int $age;
    
    #[Field(type: 'SS')] // Force a string set for a string list
    /** @var list<string> */
    private array $stringList;
    
    #[Field]
    #[DateTimeTransformer]
    private \DateTimeImmutable $createdAt;
    
    public function __construct(
        string $id = '',
        string $name = '',
        int $age = 0,
        array $stringList = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
        $this->stringList = $stringList;
        $this->createdAt = new \DateTimeImmutable();
    }
    ) {}

    // Getters and setters...
}
```
