<?php

namespace repositories;

use Illuminate\Support\ServiceProvider;
use App;

/**
 * Class RepositoriesProvider
 * @package repositories
 */
class RepositoriesProvider extends ServiceProvider
{
	protected $defer = false;

	public function boot(){
	}

	public function register(){
		App::singleton('openid\repositories\IOpenIdAssociationRepository', 'repositories\EloquentOpenIdAssociationRepository');
		App::singleton('openid\repositories\IOpenIdTrustedSiteRepository', 'repositories\EloquentOpenIdTrustedSiteRepository');
		App::singleton('auth\IUserRepository', 'repositories\EloquentUserRepository');
		App::singleton('auth\IMemberRepository', 'repositories\EloquentMemberRepository');
	}
}