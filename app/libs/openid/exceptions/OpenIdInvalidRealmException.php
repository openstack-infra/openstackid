<?php

namespace openid\exceptions;

use Exception;

class OpenIdInvalidRealmException extends Exception
{

    public function __construct($message = "")
    {
        $message = "OpenId Invalid Realm : " . $message;
        parent::__construct($message, 0, null);
    }

}