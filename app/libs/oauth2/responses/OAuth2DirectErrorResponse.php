<?php

namespace oauth2\responses;

use oauth2\OAuth2Protocol;

class OAuth2DirectErrorResponse extends OAuth2DirectResponse {
    public function __construct($error)
    {
        // Error Response: A server receiving an invalid request MUST send a
        // response with an HTTP status code of 400.
        parent::__construct(self::HttpErrorResponse, self::DirectResponseContentType);
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
    }
} 