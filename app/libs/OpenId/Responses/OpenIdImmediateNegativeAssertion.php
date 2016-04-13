<?php namespace OpenId\Responses;
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
use OpenId\OpenIdProtocol;
/**
 * Class OpenIdImmediateNegativeAssertion
 * implements @see http://openid.net/specs/openid-authentication-2_0.html#negative_assertions
 * Negative Assertions
 * In Response to Immediate Requests
 * @package OpenId\Responses
 */
class OpenIdImmediateNegativeAssertion extends OpenIdIndirectResponse
{

    /**
     * OpenIdImmediateNegativeAssertion constructor.
     * @param null|string $return_url
     */
    public function __construct($return_url = null)
    {
        parent::__construct();
        $this->setMode(OpenIdProtocol::SetupNeededMode);
        if (!is_null($return_url) && !empty($return_url)) {
            $this->setReturnTo($return_url);
        }
    }
}