<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use RuntimeException;
use Throwable;

final class RequestLogWriteException extends RuntimeException
{
    public readonly string $originalClass;

    public readonly string $originalCode;

    public function __construct(Throwable $cause)
    {
        $this->originalClass = $cause::class;
        $this->originalCode = mb_strcut((string) $cause->getCode(), 0, 64, 'UTF-8');
        $message = $cause instanceof QueryException
            ? ($cause->getPrevious()?->getMessage() ?? 'Database write failed.')
            : $cause->getMessage();

        // Do not retain the original exception chain, SQL bindings or payload.
        parent::__construct('Request log write failed: '.mb_strcut($message, 0, 1024, 'UTF-8'));
    }
}
