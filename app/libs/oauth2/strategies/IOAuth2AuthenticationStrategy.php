<?php

namespace oauth2\strategies;

use oauth2\requests\OAuth2AuthorizationRequest;

interface IOAuth2AuthenticationStrategy {

    public function doLogin(OAuth2AuthorizationRequest $request);

    public function doConsent(OAuth2AuthorizationRequest $request);
} 