<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/3/13
 * Time: 4:46 PM
 */

namespace oauth2\exceptions;
use \Exception;

class ScopeNotAllowedException extends Exception
{
    public function __construct($message = "")
    {
        $message = "Scope Not Allowed : " . $message;
        parent::__construct($message, 0, null);
    }
}