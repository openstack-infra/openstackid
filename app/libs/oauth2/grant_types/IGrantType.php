<?php

namespace oauth2\grant_types;

use oauth2\requests\OAuth2Request;

/**
 * Interface IGrantType
 * Defines a common interface for new OAuth2 Grant Types
 * @package oauth2\grant_types
 */
interface IGrantType
{

    /** Given an OAuth2Request, returns true if it can handle it, false otherwise
     * @param OAuth2Request $request
     * @return boolean
     */
    public function canHandle(OAuth2Request $request);

    /** defines entry point for first request processing
     * @param OAuth2Request $request
     * @return mixed
     */
    public function handle(OAuth2Request $request);

    /** defines entry point for final request processing
     * @param OAuth2Request $request
     * @return mixed
     */
    public function completeFlow(OAuth2Request $request);

    /**
     * get grant type
     * @return mixed
     */
    public function getType();

    /** get grant type response type
     * @return mixed
     */
    public function getResponseType();

    /** builds specific Token request
     * @param OAuth2Request $request
     * @return mixed
     */
    public function buildTokenRequest(OAuth2Request $request);
}