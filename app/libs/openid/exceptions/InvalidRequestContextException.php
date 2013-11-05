<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 4:00 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\exceptions;

use Exception;

class InvalidRequestContextException extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid Request Context : " . $message;
        parent::__construct($message, 0, null);
    }

}