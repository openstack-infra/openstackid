<?php

namespace openid\exceptions;

use Exception;

/**
 * Class InvalidOpenIdMessageException
 * @package openid\exceptions
 */
class InvalidOpenIdMessageException extends Exception {

    public function __construct($message = "")
    {
        $message = "Invalid OpenId Message : " . $message;
        parent::__construct($message, 0, null);
    }
}