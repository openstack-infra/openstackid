<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 11/13/13
 * Time: 12:32 PM
 */

namespace auth\exceptions;

use Exception;

class AuthenticationInvalidPasswordAttemptException extends Exception
{

    private $identifier;

    public function __construct($identifier,$message = "")
    {
        $message = "AuthenticationInvalidPasswordAttemptException : " . $message;
        $this->identifier = $identifier;
        parent::__construct($message, 0, null);
    }

    public function getIdentifier(){
        return $this->identifier;
    }

}