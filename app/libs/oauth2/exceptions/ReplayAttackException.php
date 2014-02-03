<?php

namespace oauth2\exceptions;

use Exception;

class ReplayAttackException extends Exception
{
    private $auth_code;

    public function getAuthCode(){
        return $this->auth_code;
    }

    public function __construct($auth_code,$message = "")
    {
        $this->auth_code = $auth_code;
        $message = "Possible Replay Attack : " . $message;
        parent::__construct($message, 0, null);
    }
}