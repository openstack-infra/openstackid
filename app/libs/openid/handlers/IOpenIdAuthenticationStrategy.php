<?php

namespace openid\handlers;

use openid\requests\OpenIdAuthenticationRequest;
use openid\requests\contexts\RequestContext;

/**
 * Interface IOpenIdAuthenticationStrategy
 * declares the contract to connect UI with OpenId protocol
 * @package openid\handlers
 */
interface IOpenIdAuthenticationStrategy {

    /**
     * Redirects to Login UI
     * @param OpenIdAuthenticationRequest $request
     * @param RequestContext $context
     * @return mixed
     */
    public function doLogin(OpenIdAuthenticationRequest $request,RequestContext $context);

    /**
     * Redirects to Consent UI
     * @param OpenIdAuthenticationRequest $request
     * @param RequestContext $context
     * @return mixed
     */
    public function doConsent(OpenIdAuthenticationRequest $request,RequestContext $context);
}