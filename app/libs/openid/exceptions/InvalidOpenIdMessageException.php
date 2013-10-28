<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 1:13 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\exceptions;
use \Exception;

class InvalidOpenIdMessageException extends Exception{

    public function __construct($message = "") {
        $message = "Invalid OpenId Message : ".$message;
        parent::__construct($message, 0 , null);
    }

}