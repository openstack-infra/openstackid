<?php

namespace openid\exceptions;

use Exception;

/**
 * Class InvalidOpenIdMessageMode
 * @package openid\exceptions
 */
class InvalidOpenIdMessageMode extends Exception {
    public function __construct($message = "")
    {
        $message = "Invalid OpenId Message Mode : " . $message;
        parent::__construct($message, 0, null);
    }
}