<?php

declare(strict_types=1);

namespace HarmonicDigital\DynamodbOdm\Transformer\Exception;

class TransformationException extends \RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
