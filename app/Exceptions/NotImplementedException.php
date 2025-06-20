<?php

namespace App\Exceptions;

use Exception;

class NotImplementedException extends Exception
{
    public function __construct(string $message = "Method not implemented", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
