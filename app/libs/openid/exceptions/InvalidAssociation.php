<?php

namespace openid\exceptions;

use Exception;

/**
 * Class InvalidAssociation
 * @package openid\exceptions
 */
class InvalidAssociation extends Exception {

    public function __construct($message = "")
    {
        $message = "Invalid Association: " . $message;
        parent::__construct($message, 0, null);
    }
}