<?php

namespace oauth2\responses;

use oauth2\OAuth2Protocol;

class OAuth2DirectErrorResponse extends OAuth2DirectResponse {
    public function __construct($error)
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpErrorResponse, self::DirectResponseContentType);
        $this[OAuth2Protocol::OAuth2Protocol_Error] = $error;
    }
} 