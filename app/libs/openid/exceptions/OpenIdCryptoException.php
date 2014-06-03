<?php

namespace openid\exceptions;

use Exception;

/**
 * Class OpenIdCryptoException
 * @package openid\exceptions
 */
class OpenIdCryptoException extends Exception {

    public function __construct($message = "")
    {
        $message = "OpenId Crypto Error: " . $message;
        parent::__construct($message, 0, null);
    }
}