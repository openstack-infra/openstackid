<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 10:36 AM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\exceptions;

use Exception;

class InvalidKVFormat extends Exception
{

    public function __construct($message = "")
    {
        $message = "Invalid Key Value Format : " . $message;
        parent::__construct($message, 0, null);
    }

}