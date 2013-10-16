<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 4:50 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid;
use Illuminate\Support\ServiceProvider;

/**
 * Class OpenIdServiceProvider
 * Register dependencies with IOC container for package openid
 * @package openid
 */
class OpenIdServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->bind('openid\IOpenIdProtocol','openid\OpenIdProtocol');
    }
}