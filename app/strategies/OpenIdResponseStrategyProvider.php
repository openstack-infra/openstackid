<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 4:38 PM
 * To change this template use File | Settings | File Templates.
 */
namespace strategies;

use Illuminate\Support\ServiceProvider;
use openid\responses\OpenIdIndirectResponse;
use openid\responses\OpenIdDirectResponse;
use openid\services\Registry;

class OpenIdResponseStrategyProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton(OpenIdDirectResponse::OpenIdDirectResponse,'strategies\\OpenIdDirectResponseStrategy');
        $this->app->singleton(OpenIdIndirectResponse::OpenIdIndirectResponse,'strategies\\OpenIdIndirectResponseStrategy');

        Registry::getInstance()->set(OpenIdDirectResponse::OpenIdDirectResponse, $this->app->make(OpenIdDirectResponse::OpenIdDirectResponse));
        Registry::getInstance()->set(OpenIdIndirectResponse::OpenIdIndirectResponse, $this->app->make(OpenIdIndirectResponse::OpenIdIndirectResponse));
    }
}