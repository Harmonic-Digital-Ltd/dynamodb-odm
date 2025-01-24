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

    /**
     * @param class-string $item  The item
     * @param string       $table The table to store the item in
     */
    public function setTable(string $item, string $table): void;
}
