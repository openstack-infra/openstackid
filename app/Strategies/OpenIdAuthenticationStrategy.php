<?php namespace Strategies;
/**
 * Copyright 2015 OpenStack Foundation
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
use Illuminate\Http\RedirectResponse;
use OpenId\Handlers\IOpenIdAuthenticationStrategy;
use OpenId\Requests\Contexts\RequestContext;
use OpenId\Requests\OpenIdAuthenticationRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
/**
 * Class OpenIdAuthenticationStrategy
 * @package Strategies
 */
final class OpenIdAuthenticationStrategy implements IOpenIdAuthenticationStrategy
{

    /**
     * @param OpenIdAuthenticationRequest $request
     * @param RequestContext $context
     * @return RedirectResponse
     */
    public function doLogin(OpenIdAuthenticationRequest $request, RequestContext $context)
    {
        Session::put('openid.auth.context', $context);
        Session::save();
        return Redirect::action('UserController@getLogin');
    }

    /**
     * @param OpenIdAuthenticationRequest $request
     * @param RequestContext $context
     * @return RedirectResponse
     */
    public function doConsent(OpenIdAuthenticationRequest $request, RequestContext $context)
    {
        Session::put('openid.auth.context', $context);
        Session::save();
        return Redirect::action('UserController@getConsent');
    }
}