<?php

namespace oauth2\exceptions;

use Exception;

class OAuth2MissingBearerAccessTokenException  extends Exception
{
    public function __construct($message = "")
    {
        $message = "Missing Bearer Access Token : " . $message;
        parent::__construct($message, 0, null);
    }
}