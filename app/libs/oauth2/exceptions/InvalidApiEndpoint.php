<?php



namespace oauth2\exceptions;

use Exception;

class InvalidApiEndpoint extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid Api Endpoint : " . $message;
        parent::__construct($message, 0, null);
    }
}