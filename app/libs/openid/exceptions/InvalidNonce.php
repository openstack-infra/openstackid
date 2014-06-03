<?php

namespace openid\exceptions;

use Exception;

/**
 * Class InvalidNonce
 * @package openid\exceptions
 */
class InvalidNonce extends Exception {

    public function __construct($message = "")
    {
        $message = "Invalid Nonce : " . $message;
        parent::__construct($message, 0, null);
    }
}