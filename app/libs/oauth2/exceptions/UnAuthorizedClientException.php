<?php

namespace oauth2\exceptions;
use \Exception;

class UnAuthorizedClientException extends Exception
{
    public function __construct($message = "")
    {
        $message = "UnAuthorized Client: " . $message;
        parent::__construct($message, 0, null);
    }
}