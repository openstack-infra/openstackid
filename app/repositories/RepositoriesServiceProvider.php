<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 4:56 PM
 * To change this template use File | Settings | File Templates.
 */
namespace repositories;

use Illuminate\Support\ServiceProvider;

class RepositoriesServiceProvider  extends ServiceProvider {

    public function register()
    {
        $this->app->bind("openid\\repositories\\IServerConfigurationRepository","repositories\ServerConfigurationRepositoryEloquent");
        $this->app->bind("openid\\repositories\\IServerExtensionsRepository","repositories\ServerExtensionsRepositoryEloquent");
    }
}