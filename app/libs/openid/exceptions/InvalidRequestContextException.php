<?php

namespace openid\exceptions;

use Exception;

/**
 * Class InvalidRequestContextException
 * @package openid\exceptions
 */
class InvalidRequestContextException extends Exception {

    public function __construct($message = "")
    {
        $message = "Invalid Request Context : " . $message;
        parent::__construct($message, 0, null);
    }
}