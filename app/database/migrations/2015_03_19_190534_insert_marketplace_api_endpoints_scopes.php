<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertMarketplaceApiEndpointsScopes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		$resource_server = ResourceServer::first();

		if(!$resource_server) return;
		// public clouds
		Api::create(
			array(
				'name'            => 'public-clouds',
				'logo'            =>  null,
				'active'          =>  true,
				'Description'     => 'Marketplace Public Clouds',
				'resource_server_id' => $resource_server->id,
				'logo'               => asset('/assets/img/apis/server.png')
			)
		);
		// private clouds
		Api::create(
			array(
				'name'            => 'private-clouds',
				'logo'            =>  null,
				'active'          =>  true,
				'Description'     => 'Marketplace Private Clouds',
				'resource_server_id' => $resource_server->id,
				'logo'               => asset('/assets/img/apis/server.png')
			)
		);
		// consultants
		Api::create(
			array(
				'name'            => 'consultants',
				'logo'            =>  null,
				'active'          =>  true,
				'Description'     => 'Marketplace Consultants',
				'resource_server_id' => $resource_server->id,
				'logo'               => asset('/assets/img/apis/server.png')
			)
		);

		$this->seedPublicCloudScopes();
		$this->seedPrivateCloudScopes();
		$this->seedConsultantScopes();
		$this->seedPublicCloudsEndpoints();
		$this->seedPrivateCloudsEndpoints();
		$this->seedConsultantsEndpoints();
	}


	private function seedPublicCloudScopes(){

		$current_realm = Config::get('app.url');
		$public_clouds    = Api::where('name','=','public-clouds')->first();

		ApiScope::create(
			array(
				'name'               => sprintf('%s/public-clouds/read',$current_realm),
				'short_description'  => 'Get Public Clouds',
				'description'        => 'Grants read only access for Public Clouds',
				'api_id'             => $public_clouds->id,
				'system'             => false,
			)
		);
	}

	private function seedPrivateCloudScopes(){

		$current_realm  = Config::get('app.url');
		$private_clouds = Api::where('name','=','private-clouds')->first();

		ApiScope::create(
			array(
				'name'               => sprintf('%s/private-clouds/read',$current_realm),
				'short_description'  => 'Get Private Clouds',
				'description'        => 'Grants read only access for Private Clouds',
				'api_id'             => $private_clouds->id,
				'system'             => false,
			)
		);
	}

	private function seedConsultantScopes(){

		$current_realm  = Config::get('app.url');
		$consultants = Api::where('name','=','consultants')->first();

		ApiScope::create(
			array(
				'name'               => sprintf('%s/consultants/read',$current_realm),
				'short_description'  => 'Get Consultants',
				'description'        => 'Grants read only access for Consultants',
				'api_id'             => $consultants->id,
				'system'             => false,
			)
		);
	}

	private function seedPublicCloudsEndpoints(){
		$public_clouds  = Api::where('name','=','public-clouds')->first();
		$current_realm  = Config::get('app.url');
		// endpoints scopes

		ApiEndpoint::create(
			array(
				'name'            => 'get-public-clouds',
				'active'          =>  true,
				'api_id'          => $public_clouds->id,
				'route'           => '/api/v1/marketplace/public-clouds',
				'http_method'     => 'GET'
			)
		);

		ApiEndpoint::create(
			array(
				'name'            => 'get-public-cloud',
				'active'          =>  true,
				'api_id'          => $public_clouds->id,
				'route'           => '/api/v1/marketplace/public-clouds/{id}',
				'http_method'     => 'GET'
			)
		);

		ApiEndpoint::create(
			array(
				'name'            => 'get-public-cloud-datacenters',
				'active'          =>  true,
				'api_id'          => $public_clouds->id,
				'route'           => '/api/v1/marketplace/public-clouds/{id}/data-centers',
				'http_method'     => 'GET'
			)
		);

		$public_cloud_read_scope           = ApiScope::where('name','=',sprintf('%s/public-clouds/read',$current_realm))->first();

		$endpoint_get_public_clouds            = ApiEndpoint::where('name','=','get-public-clouds')->first();
		$endpoint_get_public_clouds->scopes()->attach($public_cloud_read_scope->id);

		$endpoint_get_public_cloud        = ApiEndpoint::where('name','=','get-public-cloud')->first();
		$endpoint_get_public_cloud->scopes()->attach($public_cloud_read_scope->id);

		$endpoint_get_public_cloud_datacenters = ApiEndpoint::where('name','=','get-public-cloud-datacenters')->first();
		$endpoint_get_public_cloud_datacenters->scopes()->attach($public_cloud_read_scope->id);
	}

	private function seedPrivateCloudsEndpoints(){
		$private_clouds  = Api::where('name','=','private-clouds')->first();
		$current_realm  = Config::get('app.url');
		// endpoints scopes

		ApiEndpoint::create(
			array(
				'name'            => 'get-private-clouds',
				'active'          =>  true,
				'api_id'          => $private_clouds->id,
				'route'           => '/api/v1/marketplace/private-clouds',
				'http_method'     => 'GET'
			)
		);

		ApiEndpoint::create(
			array(
				'name'            => 'get-private-cloud',
				'active'          =>  true,
				'api_id'          => $private_clouds->id,
				'route'           => '/api/v1/marketplace/private-clouds/{id}',
				'http_method'     => 'GET'
			)
		);

		ApiEndpoint::create(
			array(
				'name'            => 'get-private-cloud-datacenters',
				'active'          =>  true,
				'api_id'          => $private_clouds->id,
				'route'           => '/api/v1/marketplace/private-clouds/{id}/data-centers',
				'http_method'     => 'GET'
			)
		);

		$private_cloud_read_scope           = ApiScope::where('name','=',sprintf('%s/private-clouds/read',$current_realm))->first();

		$endpoint_get_private_clouds            = ApiEndpoint::where('name','=','get-private-clouds')->first();
		$endpoint_get_private_clouds->scopes()->attach($private_cloud_read_scope->id);

		$endpoint_get_private_cloud        = ApiEndpoint::where('name','=','get-private-cloud')->first();
		$endpoint_get_private_cloud->scopes()->attach($private_cloud_read_scope->id);

		$endpoint_get_private_cloud_datacenters = ApiEndpoint::where('name','=','get-private-cloud-datacenters')->first();
		$endpoint_get_private_cloud_datacenters->scopes()->attach($private_cloud_read_scope->id);

	}

	private function seedConsultantsEndpoints(){

		$consultants  = Api::where('name','=','consultants')->first();
		$current_realm  = Config::get('app.url');
		// endpoints scopes

		ApiEndpoint::create(
			array(
				'name'            => 'get-consultants',
				'active'          =>  true,
				'api_id'          => $consultants->id,
				'route'           => '/api/v1/marketplace/consultants',
				'http_method'     => 'GET'
			)
		);

		ApiEndpoint::create(
			array(
				'name'            => 'get-consultant',
				'active'          =>  true,
				'api_id'          => $consultants->id,
				'route'           => '/api/v1/marketplace/consultants/{id}',
				'http_method'     => 'GET'
			)
		);

		ApiEndpoint::create(
			array(
				'name'            => 'get-consultant-offices',
				'active'          =>  true,
				'api_id'          => $consultants->id,
				'route'           => '/api/v1/marketplace/consultants/{id}/offices',
				'http_method'     => 'GET'
			)
		);

		$consultant_read_scope = ApiScope::where('name','=',sprintf('%s/consultants/read',$current_realm))->first();

		$endpoint              = ApiEndpoint::where('name','=','get-consultants')->first();
		$endpoint->scopes()->attach($consultant_read_scope->id);

		$endpoint              = ApiEndpoint::where('name','=','get-consultant')->first();
		$endpoint->scopes()->attach($consultant_read_scope->id);

		$endpoint              = ApiEndpoint::where('name','=','get-consultant-offices')->first();
		$endpoint->scopes()->attach($consultant_read_scope->id);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
