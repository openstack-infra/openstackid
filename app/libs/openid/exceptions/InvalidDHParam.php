<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/26/13
 * Time: 5:13 PM
 */

namespace openid\exceptions;

use Exception;

class InvalidDHParam extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid Diffie Helman Parameter : " . $message;
        parent::__construct($message, 0, null);
    }

}