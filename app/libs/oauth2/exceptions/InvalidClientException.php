<?php

namespace oauth2\exceptions;
use Exception;

class InvalidClientException extends Exception
{
    public function __construct($message = "")
    {
        $message = "Invalid OAuth2 Client : " . $message;
        parent::__construct($message, 0, null);
    }

}