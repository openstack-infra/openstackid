<?php


class ApiEndpointSeeder extends Seeder {

    public function run()
    {

        DB::table('oauth2_api_endpoint_api_scope')->delete();
        DB::table('oauth2_api_endpoint')->delete();

        $current_realm = Config::get('app.url');

        $resource_server_api        = Api::where('name','=','resource server')->first();
        $api_api                    = Api::where('name','=','api')->first();

        ApiEndpoint::create(
            array(
                'name'            => 'create resource server',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'get resource server',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource server regenerate secret',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server/regenerate-client-secret/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource server get page',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource server delete',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource server update',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource server update status',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        // endpoints api

        ApiEndpoint::create(
            array(
                'name'            => 'get endpoint',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api-endpoints/{id}',
                'http_method'     => 'GET'
            )
        );


        ApiEndpoint::create(
            array(
                'name'            => 'delete endpoint',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api-endpoints/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'create endpoint',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api-endpoints',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update endpoint',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api-endpoints',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update endpoint status',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api-endpoints/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'endpoint get page',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api-endpoints/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );

        //attach scopes to endpoints

        //resource server api scopes

        $resource_server_read_scope  = ApiScope::where('name','=',sprintf('%s/resource-server/read',$current_realm))->first();
        $resource_server_write_scope = ApiScope::where('name','=',sprintf('%s/resource-server/write',$current_realm))->first();
        $resource_server_read_page_scope  = ApiScope::where('name','=',sprintf('%s/resource-server/read.page',$current_realm))->first();
        $resource_server_regenerate_secret_scope  = ApiScope::where('name','=',sprintf('%s/resource-server/regenerate.secret',$current_realm))->first();
        $resource_server_delete_scope  = ApiScope::where('name','=',sprintf('%s/resource-server/delete',$current_realm))->first();
        $resource_server_update_scope  = ApiScope::where('name','=',sprintf('%s/resource-server/update',$current_realm))->first();
        $resource_server_update_status_scope = ApiScope::where('name','=',sprintf('%s/resource-server/update.status',$current_realm))->first();

        $resource_server_api_create = ApiEndpoint::where('name','=','create resource server')->first();
        $resource_server_api_create->scopes()->attach($resource_server_write_scope->id);

        $resource_server_api_get = ApiEndpoint::where('name','=','get resource server')->first();
        $resource_server_api_get->scopes()->attach($resource_server_read_scope->id);

        $resource_server_api_get_page = ApiEndpoint::where('name','=','resource server get page')->first();
        $resource_server_api_get_page->scopes()->attach($resource_server_read_scope->id);
        $resource_server_api_get_page->scopes()->attach($resource_server_read_page_scope->id);

        $resource_server_api_regenerate = ApiEndpoint::where('name','=','resource server regenerate secret')->first();
        $resource_server_api_regenerate->scopes()->attach($resource_server_write_scope->id);
        $resource_server_api_regenerate->scopes()->attach($resource_server_regenerate_secret_scope->id);

        $resource_server_api_delete = ApiEndpoint::where('name','=','resource server delete')->first();
        $resource_server_api_delete->scopes()->attach($resource_server_delete_scope->id);


        $resource_server_api_update = ApiEndpoint::where('name','=','resource server update')->first();
        $resource_server_api_update->scopes()->attach($resource_server_update_scope->id);

        $resource_server_api_update_status = ApiEndpoint::where('name','=','resource server update status')->first();
        $resource_server_api_update_status->scopes()->attach($resource_server_update_scope->id);
        $resource_server_api_update_status->scopes()->attach($resource_server_update_status_scope->id);

        //endpoint api scopes

        $endpoint_read_scope               = ApiScope::where('name','=',sprintf('%s/api/read',$current_realm))->first();
        $endpoint_write_scope              = ApiScope::where('name','=',sprintf('%s/api/write',$current_realm))->first();
        $endpoint_read_page_scope          = ApiScope::where('name','=',sprintf('%s/api/read.page',$current_realm))->first();
        $endpoint_delete_scope             = ApiScope::where('name','=',sprintf('%s/api/delete',$current_realm))->first();
        $endpoint_update_scope             = ApiScope::where('name','=',sprintf('%s/api/update',$current_realm))->first();
        $endpoint_update_status_scope      = ApiScope::where('name','=',sprintf('%s/api/update.status',$current_realm))->first();

        $endpoint_api_get                  = ApiEndpoint::where('name','=','get endpoint')->first();
        $endpoint_api_get->scopes()->attach($endpoint_read_scope->id);

        $endpoint_api_get_page             = ApiEndpoint::where('name','=','endpoint get page')->first();
        $endpoint_api_get_page->scopes()->attach($endpoint_read_scope->id);
        $endpoint_api_get_page->scopes()->attach($endpoint_read_page_scope->id);

        $endpoint_api_delete               = ApiEndpoint::where('name','=','delete endpoint')->first();
        $endpoint_api_delete->scopes()->attach($endpoint_delete_scope->id);

        $endpoint_api_create               = ApiEndpoint::where('name','=','create endpoint')->first();
        $endpoint_api_create->scopes()->attach($endpoint_write_scope->id);

        $endpoint_api_update               = ApiEndpoint::where('name','=','update endpoint')->first();
        $endpoint_api_update->scopes()->attach($endpoint_update_scope->id);

        $endpoint_api_update_status        = ApiEndpoint::where('name','=','update endpoint status')->first();
        $endpoint_api_update_status->scopes()->attach($endpoint_update_scope->id);
        $endpoint_api_update_status->scopes()->attach($endpoint_update_status_scope->id);
    }
}