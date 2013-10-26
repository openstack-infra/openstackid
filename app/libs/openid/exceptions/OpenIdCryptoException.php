<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/18/13
 * Time: 1:50 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\exceptions;
use \Exception;

class OpenIdCryptoException extends Exception{

    public function __construct($message = "") {
        $message = "OpenIdCryptoException : ".$message;
        parent::__construct($message, 0 , null);
    }

}