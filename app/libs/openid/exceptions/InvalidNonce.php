<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/25/13
 * Time: 11:57 AM
 */

namespace openid\exceptions;
use \Exception;

class InvalidNonce extends Exception{

    public function __construct($message = "") {
        $message = "Invalid Nonce : ".$message;
        parent::__construct($message, 0 , null);
    }

}