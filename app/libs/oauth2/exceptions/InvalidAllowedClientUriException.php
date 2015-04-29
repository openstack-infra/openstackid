<?php


namespace oauth2\exceptions;

use Exception;

class InvalidAllowedClientUriException extends Exception {

    public function __construct($message = "")
    {
        $message = "Invalid Allowed Client Uri : " . $message;
        parent::__construct($message, 0, null);
    }

}