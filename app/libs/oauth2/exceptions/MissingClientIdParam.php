<?php

namespace oauth2\exceptions;

use Exception;

class MissingClientIdParam extends Exception
{

    public function __construct($message = "")
    {
        $message = "Missing ClientId Param: " . $message;
        parent::__construct($message, 0, null);
    }

}