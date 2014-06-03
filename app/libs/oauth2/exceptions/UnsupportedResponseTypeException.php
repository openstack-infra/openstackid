<?php

namespace oauth2\exceptions;

use Exception;

class UnsupportedResponseTypeException extends Exception
{
    public function __construct($message = "")
    {
        $message = "Unsupported Response Type : " . $message;
        parent::__construct($message, 0, null);
    }
}