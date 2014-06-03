<?php

namespace openid\exceptions;

use Exception;

/**
 * Class InvalidAssociationTypeException
 * @package openid\exceptions
 */
class InvalidAssociationTypeException extends Exception {

    public function __construct($message = "")
    {
        $message = "Invalid Association Type: " . $message;
        parent::__construct($message, 0, null);
    }
}