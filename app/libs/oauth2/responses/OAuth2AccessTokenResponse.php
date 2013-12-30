<?php

namespace oauth2\responses;

use oauth2\OAuth2Protocol;


/**
 * Class OAuth2AccessTokenResponse
 * http://tools.ietf.org/html/rfc6749#section-4.1.4
 * @package oauth2\responses
 */
class OAuth2AccessTokenResponse extends OAuth2DirectResponse {

    public function __construct($access_token, $expires_in, $refresh_token=null, $scope=null)
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, self::DirectResponseContentType);

        $this[OAuth2Protocol::OAuth2Protocol_AccessToken]           = $access_token;
        $this[OAuth2Protocol::OAuth2Protocol_AccessToken_ExpiresIn] = $expires_in;
        $this[OAuth2Protocol::OAuth2Protocol_TokenType]             = 'Bearer';

        if(!is_null($refresh_token) && !empty($refresh_token))
            $this[OAuth2Protocol::OAuth2Protocol_RefreshToken] = $refresh_token;

        if(!is_null($scope) && !empty($scope))
            $this[OAuth2Protocol::OAuth2Protocol_Scope] = $scope;
    }
} 