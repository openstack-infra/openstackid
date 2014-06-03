<?php

namespace openid\exceptions;

use Exception;

/**
 * Class InvalidSessionTypeException
 * @package openid\exceptions
 */
class InvalidSessionTypeException extends Exception {

    public function __construct($message = "")
    {
        $message = "Invalid Session Type: " . $message;
        parent::__construct($message, 0, null);
    }
}