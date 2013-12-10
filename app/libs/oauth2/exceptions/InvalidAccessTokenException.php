<?php

namespace oauth2\exceptions;

use Exception;

class InvalidAccessTokenException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid Access Token : " . $message;
        parent::__construct($message, 0, null);
    }

}