<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/3/13
 * Time: 4:45 PM
 */

namespace oauth2\exceptions;
use Exception;

class UriNotAllowedException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Uri Not Allowed: " . $message;
        parent::__construct($message, 0, null);
    }

}