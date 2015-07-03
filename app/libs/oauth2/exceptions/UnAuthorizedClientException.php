<?php

namespace oauth2\exceptions;

use oauth2\OAuth2Protocol;

/**
 * Class UnAuthorizedClientException
 * @package oauth2\exceptions
 */
final class UnAuthorizedClientException extends OAuth2BaseException
{
    /**
     * @return string
     */
    public function getError()
    {
        return OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient;
    }
}