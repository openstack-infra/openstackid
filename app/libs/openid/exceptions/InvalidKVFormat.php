<?php

namespace openid\exceptions;

use Exception;

/**
 * Class InvalidKVFormat
 * @package openid\exceptions
 */
class InvalidKVFormat extends Exception {

    public function __construct($message = "")
    {
        $message = "Invalid Key Value Format : " . $message;
        parent::__construct($message, 0, null);
    }
}