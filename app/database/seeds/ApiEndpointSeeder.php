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
                'description'     => 'Creates a new Resource Server Instance',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'get resource server',
                'description'     => 'Gets Resource Server Instance',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource server regenerate secret',
                'description'     => 'Regenerate client secret of confidential application associated with a given Resource Server instance',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server/regenerate-client-secret/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource server get page',
                'description'     => 'Gets a paginated list of available resource servers',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource server delete',
                'description'     => 'Hard deletes a given resource server and all related entities (apis, endpoints, scopes)',
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
                'description'     => 'Updates attributes of given resource server',
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource server update status',
                'active'          =>  true,
                'description'     => 'Updates status (active/inactive) of a given resource server',
                'api_id'          => $resource_server_api->id,
                'route'           => '/api/v1/resource-server/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        // endpoints api

        ApiEndpoint::create(
            array(
                'name'            => 'get api',
                'active'          =>  true,
                'description'     => 'Gets a given Api by its id',
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api/{id}',
                'http_method'     => 'GET'
            )
        );


        ApiEndpoint::create(
            array(
                'name'            => 'delete api',
                'active'          =>  true,
                'description'     => 'Hard deletes a given Api and all related entities (endpoints, scopes)',
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'create api',
                'active'          =>  true,
                'description'     => 'Creates a new Api instance',
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update api',
                'description'     => 'Updates all attributes of a given api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update api status',
                'active'          =>  true,
                'description'     => 'Updates status (active/inactive) of given Api',
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'api get page',
                'description'     => 'Gets a paginated list of available Api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api/{page_nbr}/{page_size}',
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

        $api_read_scope               = ApiScope::where('name','=',sprintf('%s/api/read',$current_realm))->first();
        $api_write_scope              = ApiScope::where('name','=',sprintf('%s/api/write',$current_realm))->first();
        $api_read_page_scope          = ApiScope::where('name','=',sprintf('%s/api/read.page',$current_realm))->first();
        $api_delete_scope             = ApiScope::where('name','=',sprintf('%s/api/delete',$current_realm))->first();
        $api_update_scope             = ApiScope::where('name','=',sprintf('%s/api/update',$current_realm))->first();
        $api_update_status_scope      = ApiScope::where('name','=',sprintf('%s/api/update.status',$current_realm))->first();

        $endpoint_api_get                  = ApiEndpoint::where('name','=','get api')->first();
        $endpoint_api_get->scopes()->attach($api_read_scope->id);

        $endpoint_api_get_page             = ApiEndpoint::where('name','=','api get page')->first();
        $endpoint_api_get_page->scopes()->attach($api_read_scope->id);
        $endpoint_api_get_page->scopes()->attach($api_read_page_scope->id);

        $endpoint_api_delete               = ApiEndpoint::where('name','=','delete api')->first();
        $endpoint_api_delete->scopes()->attach($api_delete_scope->id);

        $endpoint_api_create               = ApiEndpoint::where('name','=','create api')->first();
        $endpoint_api_create->scopes()->attach($api_write_scope->id);

        $endpoint_api_update               = ApiEndpoint::where('name','=','update api')->first();
        $endpoint_api_update->scopes()->attach($api_update_scope->id);

        $endpoint_api_update_status        = ApiEndpoint::where('name','=','update api status')->first();
        $endpoint_api_update_status->scopes()->attach($api_update_scope->id);
        $endpoint_api_update_status->scopes()->attach($api_update_status_scope->id);
    }
}