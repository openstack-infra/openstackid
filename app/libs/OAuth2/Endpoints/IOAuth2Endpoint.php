<?php namespace OAuth2\Endpoints;

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
 * Interface IOAuth2Endpoint
 * @package OAuth2\Endpoints
 */
interface IOAuth2Endpoint
{
    /**
     * @param OAuth2Request $request
     * @return OAuth2Response
     */
    public function handle(OAuth2Request $request);
}