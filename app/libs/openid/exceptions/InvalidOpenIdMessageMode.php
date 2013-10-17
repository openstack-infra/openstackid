<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 3:19 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\exceptions;
use \Exception;

class InvalidOpenIdMessageMode extends Exception{

    public function __construct($message = "") {
        $message = "InvalidOpenIdMessageMode : ".$message;
        parent::__construct($message, 0 , null);
    }

}