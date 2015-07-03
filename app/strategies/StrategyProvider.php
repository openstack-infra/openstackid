<?php

namespace strategies;

use App;
use Illuminate\Support\ServiceProvider;
use oauth2\responses\OAuth2DirectResponse;
use oauth2\responses\OAuth2IndirectResponse;
use oauth2\responses\OAuth2PostResponse;
use openid\responses\OpenIdDirectResponse;
use openid\responses\OpenIdIndirectResponse;
use oauth2\responses\OAuth2IndirectFragmentResponse;
use openid\services\OpenIdServiceCatalog;
use oauth2\services\OAuth2ServiceCatalog;

/**
 * Class StrategyProvider
 * @package strategies
 */
final class StrategyProvider extends ServiceProvider
{

    public function boot()
    {
    }

    public function register()
    {
        //direct response strategy
        App::singleton(OAuth2PostResponse::OAuth2PostResponse, 'strategies\\PostResponseStrategy');
        App::singleton(OAuth2DirectResponse::OAuth2DirectResponse, 'strategies\\DirectResponseStrategy');
        App::singleton(OpenIdDirectResponse::OpenIdDirectResponse, 'strategies\\DirectResponseStrategy');
        //indirect response strategy
        App::singleton(OpenIdIndirectResponse::OpenIdIndirectResponse, 'strategies\\IndirectResponseQueryStringStrategy');
        App::singleton(OAuth2IndirectResponse::OAuth2IndirectResponse, 'strategies\\IndirectResponseQueryStringStrategy');
        App::singleton(OAuth2IndirectFragmentResponse::OAuth2IndirectFragmentResponse,'strategies\\IndirectResponseUrlFragmentStrategy');
        // authentication strategies
        App::singleton(OAuth2ServiceCatalog::AuthenticationStrategy, 'strategies\\OAuth2AuthenticationStrategy');
        App::singleton(OpenIdServiceCatalog::AuthenticationStrategy, 'strategies\\OpenIdAuthenticationStrategy');
    }

    public function provides()
    {
        return array('strategies');
    }
}