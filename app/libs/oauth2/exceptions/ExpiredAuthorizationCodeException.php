<?php

namespace oauth2\exceptions;

use Exception;

class ExpiredAuthorizationCodeException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Expired Authorization Code : " . $message;
        parent::__construct($message, 0, null);
    }

}