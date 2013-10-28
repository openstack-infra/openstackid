<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 10:35 AM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\exceptions;
use \Exception;

class InvalidOpenIdAuthenticationRequestMode extends Exception{

    public function __construct($message = "") {
        $message = "Invalid OpenId Authentication Request Mode : ".$message;
        parent::__construct($message, 0 , null);
    }

}