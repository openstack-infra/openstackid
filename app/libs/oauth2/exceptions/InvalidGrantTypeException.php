<?php

namespace oauth2\exceptions;
use Exception;

class InvalidGrantTypeException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid Grant Type : " . $message;
        parent::__construct($message, 0, null);
    }

}