<?php

namespace oauth2\exceptions;

use Exception;

class BearerTokenDisclosureAttemptException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Bearer Token Disclosure Attempt Attack: " . $message;
        parent::__construct($message, 0, null);
    }

}