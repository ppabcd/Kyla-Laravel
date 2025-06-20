<?php

namespace App\Exceptions;

use Exception;

class CacheException extends Exception
{
    public function __construct(string $message = "Cache operation failed", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
