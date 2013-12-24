<?php

namespace oauth2;
use oauth2\requests\OAuth2Request;

interface IOAuth2Protocol {
    /**
     * Authorize endpoint
     * http://tools.ietf.org/html/rfc6749#section-3.1
     * @param OAuth2Request $request
     * @return mixed
     */
    public function authorize(OAuth2Request $request);

    /**
     * Token Endpoint
     * http://tools.ietf.org/html/rfc6749#section-3.2
     * @param OAuth2Request $request
     * @return mixed
     */
    public function token(OAuth2Request $request);

    /**
     * Get all available grant types set on the protocol
     * @return mixed
     */
    public function getAvailableGrants();
} 