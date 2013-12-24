<?php

namespace oauth2\endpoints;
use oauth2\requests\OAuth2Request;

/**
 * Interface IOAuth2Endpoint
 * @package oauth2\endpoints
 */
interface IOAuth2Endpoint {
    public function handle(OAuth2Request $request);
}