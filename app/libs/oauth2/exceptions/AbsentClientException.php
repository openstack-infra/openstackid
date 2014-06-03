<?php

namespace oauth2\exceptions;

use Exception;

class AbsentClientException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Absent Client Exception: " . $message;
        parent::__construct($message, 0, null);
    }

}