<?php

namespace oauth2\exceptions;

use Exception;


class InvalidResourceServer extends Exception{

    public function __construct($message = "")
    {
        $message = "Invalid Resource Server : " . $message;
        parent::__construct($message, 0, null);
    }
}