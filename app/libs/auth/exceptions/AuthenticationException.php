<?php

namespace auth\exceptions;

use Exception;

class AuthenticationException extends Exception
{

    public function __construct($message = "")
    {
        $message = "AuthenticationException : " . $message;
        parent::__construct($message, 0, null);
    }

}