<?php

namespace App\Exceptions;

use Exception;

class NullPointerException extends Exception
{
    public function __construct(string $message = 'The data is null or undefined', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
