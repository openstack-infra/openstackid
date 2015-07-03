<?php

namespace oauth2\responses;

use oauth2\OAuth2Protocol;

/**
 * Class OAuth2AccessTokenFragmentResponse
 * @package oauth2\responses
 */
class OAuth2AccessTokenFragmentResponse extends OAuth2IndirectFragmentResponse
{

    /**
     * @param $return_to
     * @param $access_token
     * @param $expires_in
     * @param null $scope
     * @param null $state
     */
    public function __construct($return_to, $access_token, $expires_in, $scope = null, $state = null)
    {

        parent::__construct();

        $this->setReturnTo($return_to);

        if(!is_null($access_token) && !empty($access_token))
        {
            $this[OAuth2Protocol::OAuth2Protocol_AccessToken] = $access_token;
            $this[OAuth2Protocol::OAuth2Protocol_AccessToken_ExpiresIn] = $expires_in;
            $this[OAuth2Protocol::OAuth2Protocol_TokenType] = 'Bearer';
        }

        if(!is_null($scope) && !empty($scope))
            $this[OAuth2Protocol::OAuth2Protocol_Scope] = $scope;

        if(!is_null($state) && !empty($state))
            $this[OAuth2Protocol::OAuth2Protocol_State] = $state;
    }
} 