<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/24/13
 * Time: 9:22 PM
 */

namespace openid\exceptions;

use Exception;

class ReplayAttackException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Possible Replay Attack : " . $message;
        parent::__construct($message, 0, null);
    }

}