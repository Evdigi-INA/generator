<?php

namespace EvdigiIna\Generator\Exceptions;

use Exception;

class GeneratorRollbackException extends Exception
{
    public function __construct(
        string $message,
        public readonly array $createdFiles = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
