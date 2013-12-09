<?php

namespace oauth2\exceptions;

use Exception;

class InvalidAuthorizationCodeException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid Authorization Code : " . $message;
        parent::__construct($message, 0, null);
    }

}