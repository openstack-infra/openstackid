<?php

namespace oauth2\grant_types;
use oauth2\requests\OAuth2Request;

/**
 * Class ClientCredentialsGrantType
 * The client can request an access token using only its client
 * credentials (or other supported means of authentication) when the
 * client is requesting access to the protected resources under its
 * control, or those of another resource owner that have been previously
 * arranged with the authorization server (the method of which is beyond
 * the scope of this specification).
 * http://tools.ietf.org/html/rfc6749#section-4.4
 * @package oauth2\grant_types
 */
class ClientCredentialsGrantType extends AbstractGrantType {

    /** Given an OAuth2Request, returns true if it can handle it, false otherwise
     * @param OAuth2Request $request
     * @return boolean
     */
    public function canHandle(OAuth2Request $request)
    {
        // TODO: Implement canHandle() method.
    }

    /** defines entry point for first request processing
     * @param OAuth2Request $request
     * @return mixed
     */
    public function handle(OAuth2Request $request)
    {
        // TODO: Implement handle() method.
    }

    /**
     * get grant type
     * @return mixed
     */
    public function getType()
    {
        // TODO: Implement getType() method.
    }

    /** get grant type response type
     * @return mixed
     */
    public function getResponseType()
    {
        // TODO: Implement getResponseType() method.
    }

    /** builds specific Token request
     * @param OAuth2Request $request
     * @return mixed
     */
    public function buildTokenRequest(OAuth2Request $request)
    {
        // TODO: Implement buildTokenRequest() method.
    }
}