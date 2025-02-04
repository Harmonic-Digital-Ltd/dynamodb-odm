<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Model;

use HarmonicDigital\DynamodbOdm\Attribute\Field;
use HarmonicDigital\DynamodbOdm\Attribute\Item;
use HarmonicDigital\DynamodbOdm\Attribute\PartitionKey;
use HarmonicDigital\DynamodbOdm\Transformer\DateTimeTransformer;

#[Item]
readonly class DateTimeObject
{
    public function __construct(
        #[Field]
        #[PartitionKey]
        public string $id = 'id',
        #[Field]
        #[DateTimeTransformer]
        public \DateTime $dateTime = new \DateTime('2021-01-01 00:00:00.654321'),
        #[Field]
        #[DateTimeTransformer(\DateTimeInterface::ATOM)]
        public \DateTimeImmutable $dateTimeImmutable = new \DateTimeImmutable('2021-01-02 00:00:00'),
        #[Field]
        #[DateTimeTransformer]
        public \DateTimeInterface $dateTimeInterface = new \DateTime('2021-01-03 00:00:00'),
        public mixed $undefinedDateTime = new \DateTime('2021-01-03 00:00:00'),
        public string $notDateTime = '2021-01-03 00:00:00',
    ) {}
}
