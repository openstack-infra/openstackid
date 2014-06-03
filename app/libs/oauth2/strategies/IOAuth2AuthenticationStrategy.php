<?php

namespace oauth2\strategies;

use oauth2\requests\OAuth2AuthorizationRequest;

/**
 * Interface IOAuth2AuthenticationStrategy
 * @package oauth2\strategies
 */
interface IOAuth2AuthenticationStrategy {

    public function doLogin(OAuth2AuthorizationRequest $request);

    public function doConsent(OAuth2AuthorizationRequest $request);
} 