<?php

namespace openid\exceptions;

use Exception;

/**
 * Class InvalidOpenIdAuthenticationRequestMode
 * @package openid\exceptions
 */
class InvalidOpenIdAuthenticationRequestMode extends Exception {
    public function __construct($message = "")
    {
        $message = "Invalid OpenId Authentication Request Mode : " . $message;
        parent::__construct($message, 0, null);
    }
}