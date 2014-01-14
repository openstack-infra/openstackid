<?php

namespace auth\exceptions;

use Exception;

class AuthenticationException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Authentication Exception : " . $message;
        parent::__construct($message, 0, null);
    }

}