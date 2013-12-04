<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/4/13
 * Time: 11:08 AM
 */

namespace oauth2\strategies;


use oauth2\requests\OAuth2AuthorizationRequest;

interface IOAuth2AuthenticationStrategy {

    public function doLogin(OAuth2AuthorizationRequest $request);

    public function doConsent(OAuth2AuthorizationRequest $request);
} 