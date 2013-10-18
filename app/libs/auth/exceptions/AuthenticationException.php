<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/18/13
 * Time: 12:06 PM
 * To change this template use File | Settings | File Templates.
 */

namespace auth\exceptions;

use \Exception;

class AuthenticationException extends Exception{

    public function __construct($message = "") {
        $message = "AuthenticationException : ".$message;
        parent::__construct($message, 0 , null);
    }

}