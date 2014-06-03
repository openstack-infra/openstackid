<?php

namespace oauth2\exceptions;

use Exception;

class ScopeNotAllowedException extends Exception
{
    public function __construct($message = "")
    {
        $message = "Scope Not Allowed : " . $message;
        parent::__construct($message, 0, null);
    }
}