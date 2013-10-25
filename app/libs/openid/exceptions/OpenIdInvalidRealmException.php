<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/25/13
 * Time: 1:23 PM
 */

namespace openid\exceptions;
use \Exception;

class OpenIdInvalidRealmException  extends Exception{

    public function __construct($message = "") {
        $message = "OpenIdInvalidRealmException : ".$message;
        parent::__construct($message, 0 , null);
    }

}