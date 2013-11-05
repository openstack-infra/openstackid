<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/28/13
 * Time: 6:03 PM
 */

namespace openid\exceptions;

use Exception;

class InvalidAssociationTypeException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid Association Type: " . $message;
        parent::__construct($message, 0, null);
    }

}