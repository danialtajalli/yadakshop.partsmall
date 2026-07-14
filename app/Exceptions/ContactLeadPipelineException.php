<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class ContactLeadPipelineException extends RuntimeException
{
    public function __construct(
        public readonly string $reason,
        ?Throwable $previous = null,
    ) {
        parent::__construct($reason, 0, $previous);
    }
}
