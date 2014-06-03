<?php

namespace openid\exceptions;

use Exception;

/**
 * Class InvalidTLDException
 * @package openid\exceptions
 */
class InvalidTLDException extends Exception {

    public function __construct($message = "")
    {
        $message = "Invalid TDL: " . $message;
        parent::__construct($message, 0, null);
    }
}