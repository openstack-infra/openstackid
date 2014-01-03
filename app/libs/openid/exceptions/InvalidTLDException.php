<?php

namespace openid\exceptions;

use Exception;

class InvalidTLDException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid TDL: " . $message;
        parent::__construct($message, 0, null);
    }

}