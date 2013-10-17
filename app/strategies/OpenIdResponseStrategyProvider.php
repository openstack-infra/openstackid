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

class OpenIdResponseStrategyProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('OpenIdDirectResponseStrategy','strategies\\OpenIdDirectResponseStrategy');
        $this->app->singleton('OpenIdIndirectResponseStrategy','strategies\\OpenIdIndirectResponseStrategy');
    }
}