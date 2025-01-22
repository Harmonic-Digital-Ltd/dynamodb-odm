<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm;

interface ItemManagerInterface
{
    public function put(object $object): void;

    public function delete(object $item): void;

    /**
     * @template T as object
     *
     * @param class-string<T> $className
     *
     * @return null|T
     */
    public function getItem(string $className, mixed $partitionKey, mixed $sortKey = null): ?object;

    public function createTable(string $className): void;
}
