<?php namespace OpenId\Handlers;
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
use OpenId\Requests\OpenIdAuthenticationRequest;
use OpenId\Requests\Contexts\RequestContext;
/**
 * Interface IOpenIdAuthenticationStrategy
 * declares the contract to connect UI with OpenId protocol
 * @package OpenId\Handlers
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