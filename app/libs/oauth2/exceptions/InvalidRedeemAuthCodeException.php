<?php

namespace oauth2\exceptions;

use oauth2\OAuth2Protocol;

/**
 * Class InvalidRedeemAuthCodeException
 * @package oauth2\exceptions
 */
final class InvalidRedeemAuthCodeException extends OAuth2BaseException
{
    /**
     * @return string
     */
    public function getError()
    {
        return OAuth2Protocol::OAuth2Protocol_Error_InvalidGrant;
    }
}