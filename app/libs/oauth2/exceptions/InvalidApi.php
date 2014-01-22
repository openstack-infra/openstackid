<?php

namespace oauth2\exceptions;

use Exception;

class InvalidApi extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid Api : " . $message;
        parent::__construct($message, 0, null);
    }

}