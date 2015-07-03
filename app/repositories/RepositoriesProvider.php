<?php

namespace repositories;

use App;
use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoriesProvider
 * @package repositories
 */
class RepositoriesProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
    }

    public function register()
    {
        App::singleton('openid\repositories\IOpenIdAssociationRepository', 'repositories\EloquentOpenIdAssociationRepository');
        App::singleton('openid\repositories\IOpenIdTrustedSiteRepository', 'repositories\EloquentOpenIdTrustedSiteRepository');
        App::singleton('auth\IUserRepository', 'repositories\EloquentUserRepository');
        App::singleton('auth\IMemberRepository', 'repositories\EloquentMemberRepository');
        App::singleton('oauth2\repositories\IClientPublicKeyRepository', 'repositories\EloquentClientPublicKeyRepository');
        App::singleton('oauth2\repositories\IServerPrivateKeyRepository', 'repositories\EloquentServerPrivateKeyRepository');
        App::singleton('oauth2\repositories\IClientRepository', 'repositories\EloquentClientRepository');
    }
}