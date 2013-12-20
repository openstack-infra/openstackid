<?php

namespace oauth2\responses;

use oauth2\OAuth2Protocol;

class OAuth2AccessTokenValidationResponse extends OAuth2DirectResponse {

    public function __construct($access_token,$scope, $audience)
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, self::DirectResponseContentType);
        $this[OAuth2Protocol::OAuth2Protocol_AccessToken]           = $access_token;
        $this[OAuth2Protocol::OAuth2Protocol_TokenType]             = 'Bearer';
        $this[OAuth2Protocol::OAuth2Protocol_Scope]                 = $scope;
        $this[OAuth2Protocol::OAuth2Protocol_Audience]              = $audience;
    }
} 