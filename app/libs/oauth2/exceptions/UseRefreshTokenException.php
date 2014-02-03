<?php


namespace oauth2\exceptions;
use Exception;

class UseRefreshTokenException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Use Refresh Token Exception: " . $message;
        parent::__construct($message, 0, null);
    }

}