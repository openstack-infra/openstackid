<?php

namespace strategies;

use Illuminate\Support\ServiceProvider;
use oauth2\responses\OAuth2DirectResponse;
use oauth2\responses\OAuth2IndirectResponse;
use openid\responses\OpenIdDirectResponse;
use openid\responses\OpenIdIndirectResponse;
use oauth2\responses\OAuth2IndirectFragmentResponse;

class StrategyProvider extends ServiceProvider
{

    public function boot()
    {
    }

    public function register()
    {
        //direct response strategy
        $this->app->singleton(OAuth2DirectResponse::OAuth2DirectResponse, 'strategies\\DirectResponseStrategy');
        $this->app->singleton(OpenIdDirectResponse::OpenIdDirectResponse, 'strategies\\DirectResponseStrategy');
        //indirect response strategy
        $this->app->singleton(OpenIdIndirectResponse::OpenIdIndirectResponse, 'strategies\\IndirectResponseQueryStringStrategy');
        $this->app->singleton(OAuth2IndirectResponse::OAuth2IndirectResponse, 'strategies\\IndirectResponseQueryStringStrategy');
        $this->app->singleton(OAuth2IndirectFragmentResponse::OAuth2IndirectFragmentResponse,'strategies\\IndirectResponseUrlFragmentStrategy');
        $this->app->singleton('oauth2\\strategies\\IOAuth2AuthenticationStrategy', 'strategies\\OAuth2AuthenticationStrategy');
    }

    public function provides()
    {
        return array('strategies');
    }
}