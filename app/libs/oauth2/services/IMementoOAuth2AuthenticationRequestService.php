<?php

namespace oauth2\services;
use oauth2\requests\OAuth2AuthorizationRequest;
use oauth2\requests\OAuth2AccessTokenRequest;

interface IMementoOAuth2AuthenticationRequestService {
    /**
     * Save current OAuth2AuthorizationRequest till next request
     * @return bool
     */
    public function saveCurrentAuthorizationRequest();

    /** Retrieve last OpenIdMessage
     * @return OAuth2AuthorizationRequest;
     */
    public function getCurrentAuthorizationRequest();

    /**
     * @return OAuth2AccessTokenRequest
     */
    public function getCurrentAccessTokenRequest();

    public function clearCurrentRequest();
} 