<?php

namespace oauth2\exceptions;

use \Exception;

class AccessDeniedException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Access Denied : " . $message;
        parent::__construct($message, 0, null);
    }

}