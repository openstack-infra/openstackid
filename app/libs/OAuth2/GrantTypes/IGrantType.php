<?php namespace OAuth2\GrantTypes;

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
use OAuth2\Responses\OAuth2Response;

/**
 * Interface IGrantType
 * Defines a common interface for new OAuth2 Grant Types
 * @package OAuth2\GrantTypes
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
     * @return OAuth2Response
     */
    public function handle(OAuth2Request $request);

    /** defines entry point for final request processing
     * @param OAuth2Request $request
     * @return OAuth2Response
     */
    public function completeFlow(OAuth2Request $request);

    /**
     * get grant type
     * @return string
     */
    public function getType();

    /**
     * get grant type response type
     * @return array
     */
    public function getResponseType();

    /** builds specific Token request
     * @param OAuth2Request $request
     * @return OAuth2Response
     */
    public function buildTokenRequest(OAuth2Request $request);
}