<?php

namespace oauth2\exceptions;

use Exception;

class OAuth2GenericException extends Exception
{

    public function __construct($message = "")
    {
        $message = "OAuth2 Generic Exception : " . $message;
        parent::__construct($message, 0, null);
    }

}