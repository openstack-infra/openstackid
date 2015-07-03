<?php

namespace oauth2\exceptions;

use Exception;
use oauth2\OAuth2Protocol;

/**
 * Class MissingClientAuthorizationInfo
 * @package oauth2\exceptions
 */
final class MissingClientAuthorizationInfo extends OAuth2BaseException
{
    /**
     * @return string
     */
    public function getError()
    {
        return OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient;
    }
}