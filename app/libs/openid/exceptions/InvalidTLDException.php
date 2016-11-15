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
        $message = "Invalid Top Level Domain: " . $message;
        parent::__construct($message, 0, null);
    }
}
