<?php

namespace App\Exceptions;

use Exception;

class InvalidJsonStringException extends Exception
{
    public function __construct(array $params = [], int $code = 0, Exception $previous = null)
    {
        $message = "Invalid JSON string provided: " . implode(', ', $params);
        parent::__construct($message, $code, $previous);
    }
}
