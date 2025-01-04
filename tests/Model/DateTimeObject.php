<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Test\Model;

readonly class DateTimeObject
{
    public function __construct(
        public \DateTime $dateTime = new \DateTime('2021-01-01 00:00:00.654321'),
        public \DateTimeImmutable $dateTimeImmutable = new \DateTimeImmutable('2021-01-02 00:00:00'),
        public \DateTimeInterface $dateTimeInterface = new \DateTime('2021-01-03 00:00:00'),
        public mixed $undefinedDateTime = new \DateTime('2021-01-03 00:00:00'),
        public string $notDateTime = '2021-01-03 00:00:00',
    ) {}
}
