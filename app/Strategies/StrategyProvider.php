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
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use OAuth2\Responses\OAuth2DirectResponse;
use OAuth2\Responses\OAuth2IndirectResponse;
use OAuth2\Responses\OAuth2PostResponse;
use OpenId\Responses\OpenIdDirectResponse;
use OpenId\Responses\OpenIdIndirectResponse;
use OAuth2\Responses\OAuth2IndirectFragmentResponse;
use OpenId\Services\OpenIdServiceCatalog;
use OAuth2\Services\OAuth2ServiceCatalog;

/**
 * Class StrategyProvider
 * @package Strategies
 */
final class StrategyProvider extends ServiceProvider
{

    protected $defer = true;

    public function boot()
    {
    }

    public function register()
    {
        //direct response strategy
        App::singleton(OAuth2PostResponse::OAuth2PostResponse, \Strategies\PostResponseStrategy::class);
        App::singleton(OAuth2DirectResponse::OAuth2DirectResponse, \Strategies\DirectResponseStrategy::class);
        App::singleton(OpenIdDirectResponse::OpenIdDirectResponse, \Strategies\DirectResponseStrategy::class);
        //indirect response strategy
        App::singleton(OpenIdIndirectResponse::OpenIdIndirectResponse, \Strategies\IndirectResponseQueryStringStrategy::class);
        App::singleton(OAuth2IndirectResponse::OAuth2IndirectResponse, \Strategies\IndirectResponseQueryStringStrategy::class);
        App::singleton(OAuth2IndirectFragmentResponse::OAuth2IndirectFragmentResponse,\Strategies\IndirectResponseUrlFragmentStrategy::class);
        // authentication strategies
        App::singleton(OAuth2ServiceCatalog::AuthenticationStrategy, \Strategies\OAuth2AuthenticationStrategy::class);
        App::singleton(OpenIdServiceCatalog::AuthenticationStrategy, \Strategies\OpenIdAuthenticationStrategy::class);
    }

    public function provides()
    {
        return [
            OAuth2PostResponse::OAuth2PostResponse,
            OAuth2DirectResponse::OAuth2DirectResponse,
            OpenIdDirectResponse::OpenIdDirectResponse,
            OpenIdIndirectResponse::OpenIdIndirectResponse,
            OAuth2IndirectResponse::OAuth2IndirectResponse,
            OAuth2IndirectFragmentResponse::OAuth2IndirectFragmentResponse,
            OAuth2ServiceCatalog::AuthenticationStrategy,
            OpenIdServiceCatalog::AuthenticationStrategy,
        ];
    }
}