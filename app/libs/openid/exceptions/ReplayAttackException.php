<?php

namespace openid\exceptions;

use Exception;

/**
 * Class ReplayAttackException
 * @package openid\exceptions
 */
class ReplayAttackException extends Exception {

    public function __construct($message = "")
    {
        $message = "Possible Replay Attack : " . $message;
        parent::__construct($message, 0, null);
    }
}