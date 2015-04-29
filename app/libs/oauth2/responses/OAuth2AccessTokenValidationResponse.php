<?php

namespace oauth2\responses;

use oauth2\OAuth2Protocol;

class OAuth2AccessTokenValidationResponse extends OAuth2DirectResponse {

    public function __construct($access_token,$scope, $audience, $client_id, $expires_in, $user_id = null, $client_type = null, $allowed_urls = array(), $allowed_origins = array())
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, self::DirectResponseContentType);
        $this[OAuth2Protocol::OAuth2Protocol_AccessToken]           = $access_token;
        $this[OAuth2Protocol::OAuth2Protocol_ClientId]              = $client_id;
        $this[OAuth2Protocol::OAuth2Protocol_TokenType]             = 'Bearer';
        $this[OAuth2Protocol::OAuth2Protocol_Scope]                 = $scope;
        $this[OAuth2Protocol::OAuth2Protocol_Audience]              = $audience;
        $this[OAuth2Protocol::OAuth2Protocol_AccessToken_ExpiresIn] = $expires_in;

        if(!is_null($user_id)){
            $this[OAuth2Protocol::OAuth2Protocol_UserId] = $user_id;
        }

        if(!is_null($client_type)){
            $this['client_type'] = $client_type;
        }

        if(count($allowed_urls)){
            $this['allowed_return_uris'] = implode(' ', $allowed_urls);
        }

        if(count($allowed_origins)){
            $this['allowed_origins'] = implode(' ', $allowed_origins);
        }
    }
} 