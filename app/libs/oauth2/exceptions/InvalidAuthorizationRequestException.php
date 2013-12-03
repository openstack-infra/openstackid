<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/3/13
 * Time: 10:04 AM
 */

namespace oauth2\exceptions;

use Exception;

class InvalidAuthorizationRequestException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid Authorization Request : " . $message;
        parent::__construct($message, 0, null);
    }

}