<?php

namespace oauth2\exceptions;

use Exception;
use oauth2\OAuth2Protocol;

/**
 * Class UriNotAllowedException
 * @package oauth2\exceptions
 */
class UriNotAllowedException extends OAuth2BaseException
{

    public function __construct($redirect_url = "")
    {
        $message = sprintf("The redirect URI in the request: %s did not match a registered redirect URI.",$redirect_url);
        parent::__construct($message, 0, null);
    }

    public function getError()
    {
        return OAuth2Protocol::OAuth2Protocol_Error_RedirectUriMisMatch;
    }
}