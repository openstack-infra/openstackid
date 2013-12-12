<?php

namespace oauth2\exceptions;
use Exception;

class AllowedClientUriAlreadyExistsException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Allowed Client Uri Already Exists : " . $message;
        parent::__construct($message, 0, null);
    }

}