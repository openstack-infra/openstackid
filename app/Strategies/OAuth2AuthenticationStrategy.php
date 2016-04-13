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
use OAuth2\Requests\OAuth2AuthorizationRequest;
use OAuth2\Strategies\IOAuth2AuthenticationStrategy;
use Illuminate\Support\Facades\Redirect;
/**
 * Class OAuth2AuthenticationStrategy
 * @package Strategies
 */
class OAuth2AuthenticationStrategy implements IOAuth2AuthenticationStrategy {

    public function doLogin(OAuth2AuthorizationRequest $request)
    {
        return Redirect::action('UserController@getLogin');
    }

    public function doConsent(OAuth2AuthorizationRequest $request)
    {
        return Redirect::action('UserController@getConsent');
    }
}