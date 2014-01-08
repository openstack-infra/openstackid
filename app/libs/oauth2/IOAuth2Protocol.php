<?php

namespace oauth2;
use oauth2\requests\OAuth2Request;
use oauth2\OAuth2Message;

interface IOAuth2Protocol {
    /**
     * Authorize endpoint
     * http://tools.ietf.org/html/rfc6749#section-3.1
     * @param OAuth2Request $request
     * @return mixed
     */
    public function authorize(OAuth2Request $request = null);

    /**
     * Token Endpoint
     * http://tools.ietf.org/html/rfc6749#section-3.2
     * @param OAuth2Request $request
     * @return mixed
     */
    public function token(OAuth2Request $request = null);

    /**
     * Revoke Token Endpoint
     * http://tools.ietf.org/html/rfc7009
     * @param OAuth2Request $request
     * @return mixed
     */
    public function revoke(OAuth2Request $request = null);

    /**
     * Introspection Token Endpoint
     * http://tools.ietf.org/html/draft-richer-oauth-introspection-04
     * @param OAuth2Request $request
     * @return mixed
     */
    public function introspection(OAuth2Request $request = null);



    /**
     * Get all available grant types set on the protocol
     * @return mixed
     */
    public function getAvailableGrants();
} 