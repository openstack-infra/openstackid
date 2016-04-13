<?php namespace OAuth2;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use OAuth2\Requests\OAuth2Request;
/**
 * Interface IOAuth2Protocol
 * @package OAuth2
 */
interface IOAuth2Protocol {
    /**
     * Authorize endpoint
     * @see http://tools.ietf.org/html/rfc6749#section-3.1
     * @param OAuth2Request $request
     * @return mixed
     */
    public function authorize(OAuth2Request $request = null);

    /**
     * Token Endpoint
     * @see http://tools.ietf.org/html/rfc6749#section-3.2
     * @param OAuth2Request $request
     * @return mixed
     */
    public function token(OAuth2Request $request = null);

    /**
     * Revoke Token Endpoint
     * @see http://tools.ietf.org/html/rfc7009
     * @param OAuth2Request $request
     * @return mixed
     */
    public function revoke(OAuth2Request $request = null);

    /**
     * Introspection Token Endpoint
     * @see http://tools.ietf.org/html/draft-richer-oauth-introspection-04
     * @param OAuth2Request $request
     * @return mixed
     */
    public function introspection(OAuth2Request $request = null);

    /**
     * Get all available grant types set on the protocol
     * @return mixed
     */
    public function getAvailableGrants();

    /**
     * @return string
     */
    public function getJWKSDocument();

    /**
     * @see http://openid.net/specs/openid-connect-discovery-1_0.html
     * @return string
     */
    public function getDiscoveryDocument();


    /**
     * @see http://openid.net/specs/openid-connect-session-1_0.html#RPLogout
     */
    public function endSession(OAuth2Request $request = null);

    /**
     * @return OAuth2Request
     */
    public function getLastRequest();

} 