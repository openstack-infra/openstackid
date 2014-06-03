<?php

namespace oauth2\services;

use oauth2\requests\OAuth2AuthorizationRequest;

/**
 * Interface IMementoOAuth2AuthenticationRequestService
 * @package oauth2\services
 */
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


    public function clearCurrentRequest();
} 