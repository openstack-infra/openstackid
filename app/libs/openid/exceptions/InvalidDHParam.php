<?php

namespace openid\exceptions;

use Exception;

/**
 * Class InvalidDHParam
 * @package openid\exceptions
 */
class InvalidDHParam extends Exception {

    public function __construct($message = "")
    {
        $message = "Invalid Diffie Helman Parameter : " . $message;
        parent::__construct($message, 0, null);
    }
}