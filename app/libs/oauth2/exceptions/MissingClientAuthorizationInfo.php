<?php

namespace oauth2\exceptions;

use Exception;

class MissingClientAuthorizationInfo extends Exception
{

    public function __construct($message = "")
    {
        $message = "Missing Client Authorization Info: " . $message;
        parent::__construct($message, 0, null);
    }

}