<?php

namespace oauth2\services;
use oauth2\requests\OAuth2AuthorizationRequest;

interface IMementoOAuth2AuthenticationRequestService {
    /**
     * Save current OAuth2AuthorizationRequest till next request
     * @return bool
     */
    public function saveCurrentRequest();

    /** Retrieve last OpenIdMessage
     * @return OAuth2AuthorizationRequest;
     */
    public function getCurrentRequest();

    public function clearCurrentRequest();
} 