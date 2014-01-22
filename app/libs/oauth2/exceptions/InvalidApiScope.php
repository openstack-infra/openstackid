<?php

namespace oauth2\exceptions;

use Exception;

class InvalidApiScope extends Exception
{

    public function __construct($message = "")
{
    $message = "Invalid Api Scope : " . $message;
    parent::__construct($message, 0, null);
}
}