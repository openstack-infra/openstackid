<?php

namespace oauth2\exceptions;

use Exception;

class ReplayAttackException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Possible Replay Attack : " . $message;
        parent::__construct($message, 0, null);
    }

}