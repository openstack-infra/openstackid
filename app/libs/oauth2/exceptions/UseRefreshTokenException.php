<?php

namespace oauth2\exceptions;


use oauth2\OAuth2Protocol;

/**
 * Class UseRefreshTokenException
 * @package oauth2\exceptions
 */
class UseRefreshTokenException extends OAuth2BaseException
{

    public function getError()
    {
        return OAuth2Protocol::OAuth2Protocol_Error_InvalidGrant;
    }
}