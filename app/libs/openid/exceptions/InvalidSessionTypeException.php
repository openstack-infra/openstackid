<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/28/13
 * Time: 7:11 PM
 */

namespace openid\exceptions;
use \Exception;

class InvalidSessionTypeException extends  Exception{

    public function __construct($message = "") {
        $message = "Invalid Session Type: ".$message;
        parent::__construct($message, 0 , null);
    }

}