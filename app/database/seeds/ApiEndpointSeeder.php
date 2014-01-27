<?php


class ApiEndpointSeeder extends Seeder {

    public function run()
    {

        DB::table('oauth2_api_endpoint_api_scope')->delete();
        DB::table('oauth2_api_endpoint')->delete();
        $this->seedResourceServerEndpoints();
        $this->seedApiEndpoints();
        $this->seedApiEndpointEndpoints();
        $this->seedScopeEndpoints();
    }

    private function seedResourceServerEndpoints(){

        $current_realm  = Config::get('app.url');
        $resource_server = Api::where('name','=','resource-server')->first();

        ApiEndpoint::create(
            array(
                'name'            => 'create-resource-server',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => 'api/v1/resource-server',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'get-resource-server',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => 'api/v1/resource-server/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-regenerate-secret',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => 'api/v1/resource-server/regenerate-client-secret/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-get-page',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => 'api/v1/resource-server/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-delete',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => 'api/v1/resource-server/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-update',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => 'api/v1/resource-server',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-update-status',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => 'api/v1/resource-server/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        //attach scopes to endpoints

        //resource server api scopes

        $resource_server_read_scope               = ApiScope::where('name','=',sprintf('%s/resource-server/read',$current_realm))->first();
        $resource_server_write_scope              = ApiScope::where('name','=',sprintf('%s/resource-server/write',$current_realm))->first();
        $resource_server_read_page_scope          = ApiScope::where('name','=',sprintf('%s/resource-server/read.page',$current_realm))->first();
        $resource_server_regenerate_secret_scope  = ApiScope::where('name','=',sprintf('%s/resource-server/regenerate.secret',$current_realm))->first();
        $resource_server_delete_scope             = ApiScope::where('name','=',sprintf('%s/resource-server/delete',$current_realm))->first();
        $resource_server_update_scope             = ApiScope::where('name','=',sprintf('%s/resource-server/update',$current_realm))->first();
        $resource_server_update_status_scope      = ApiScope::where('name','=',sprintf('%s/resource-server/update.status',$current_realm))->first();


        // create needs write access
        $resource_server_api_create = ApiEndpoint::where('name','=','create-resource-server')->first();
        $resource_server_api_create->scopes()->attach($resource_server_write_scope->id);

        //get needs read access
        $resource_server_api_get = ApiEndpoint::where('name','=','get-resource-server')->first();
        $resource_server_api_get->scopes()->attach($resource_server_read_scope->id);

        // get page needs read access or read page access
        $resource_server_api_get_page = ApiEndpoint::where('name','=','resource-server-get-page')->first();
        $resource_server_api_get_page->scopes()->attach($resource_server_read_scope->id);
        $resource_server_api_get_page->scopes()->attach($resource_server_read_page_scope->id);

        //regenerate secret needs write access or specific access
        $resource_server_api_regenerate = ApiEndpoint::where('name','=','resource-server-regenerate-secret')->first();
        $resource_server_api_regenerate->scopes()->attach($resource_server_write_scope->id);
        $resource_server_api_regenerate->scopes()->attach($resource_server_regenerate_secret_scope->id);

        //deletes needs delete access
        $resource_server_api_delete = ApiEndpoint::where('name','=','resource-server-delete')->first();
        $resource_server_api_delete->scopes()->attach($resource_server_delete_scope->id);

        //update needs update access
        $resource_server_api_update = ApiEndpoint::where('name','=','resource-server-update')->first();
        $resource_server_api_update->scopes()->attach($resource_server_update_scope->id);

        //update status needs update access or specific access
        $resource_server_api_update_status = ApiEndpoint::where('name','=','resource-server-update-status')->first();
        $resource_server_api_update_status->scopes()->attach($resource_server_update_scope->id);
        $resource_server_api_update_status->scopes()->attach($resource_server_update_status_scope->id);


    }

    private function seedApiEndpoints(){

        $current_realm  = Config::get('app.url');
        $api_api = Api::where('name','=','api')->first();

        ApiEndpoint::create(
            array(
                'name'            => 'get-api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => 'api/v1/api/{id}',
                'http_method'     => 'GET'
            )
        );


        ApiEndpoint::create(
            array(
                'name'            => 'delete-api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => 'api/v1/api/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'create-api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => 'api/v1/api',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => 'api/v1/api',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-api-status',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => 'api/v1/api/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'api-get-page',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => 'api/v1/api/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );

        //endpoint api scopes

        $api_read_scope               = ApiScope::where('name','=',sprintf('%s/api/read',$current_realm))->first();
        $api_write_scope              = ApiScope::where('name','=',sprintf('%s/api/write',$current_realm))->first();
        $api_read_page_scope          = ApiScope::where('name','=',sprintf('%s/api/read.page',$current_realm))->first();
        $api_delete_scope             = ApiScope::where('name','=',sprintf('%s/api/delete',$current_realm))->first();
        $api_update_scope             = ApiScope::where('name','=',sprintf('%s/api/update',$current_realm))->first();
        $api_update_status_scope      = ApiScope::where('name','=',sprintf('%s/api/update.status',$current_realm))->first();

        $endpoint_api_get                  = ApiEndpoint::where('name','=','get-api')->first();
        $endpoint_api_get->scopes()->attach($api_read_scope->id);

        $endpoint_api_get_page             = ApiEndpoint::where('name','=','api-get-page')->first();
        $endpoint_api_get_page->scopes()->attach($api_read_scope->id);
        $endpoint_api_get_page->scopes()->attach($api_read_page_scope->id);

        $endpoint_api_delete               = ApiEndpoint::where('name','=','delete-api')->first();
        $endpoint_api_delete->scopes()->attach($api_delete_scope->id);

        $endpoint_api_create               = ApiEndpoint::where('name','=','create-api')->first();
        $endpoint_api_create->scopes()->attach($api_write_scope->id);

        $endpoint_api_update               = ApiEndpoint::where('name','=','update-api')->first();
        $endpoint_api_update->scopes()->attach($api_update_scope->id);

        $endpoint_api_update_status        = ApiEndpoint::where('name','=','update-api-status')->first();
        $endpoint_api_update_status->scopes()->attach($api_update_scope->id);
        $endpoint_api_update_status->scopes()->attach($api_update_status_scope->id);
    }

    private function seedApiEndpointEndpoints(){

        $current_realm  = Config::get('app.url');
        $api_api_endpoint           = Api::where('name','=','api-endpoint')->first();

        ApiEndpoint::create(
            array(
                'name'            => 'get-api-endpoint',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'delete-api-endpoint',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'create-api-endpoint',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-api-endpoint',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-api-endpoint-status',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'api-endpoint-get-page',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );


        ApiEndpoint::create(
            array(
                'name'            => 'add-api-endpoint-scope',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint/scope/add/{id}/{scope_id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'remove-api-endpoint-scope',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint/scope/remove/{id}/{scope_id}',
                'http_method'     => 'GET'
            )
        );

        //endpoint api endpoint scopes

        $api_endpoint_read_scope               = ApiScope::where('name','=',sprintf('%s/api-endpoint/read',$current_realm))->first();
        $api_endpoint_write_scope              = ApiScope::where('name','=',sprintf('%s/api-endpoint/write',$current_realm))->first();
        $api_endpoint_read_page_scope          = ApiScope::where('name','=',sprintf('%s/api-endpoint/read.page',$current_realm))->first();
        $api_endpoint_delete_scope             = ApiScope::where('name','=',sprintf('%s/api-endpoint/delete',$current_realm))->first();
        $api_endpoint_update_scope             = ApiScope::where('name','=',sprintf('%s/api-endpoint/update',$current_realm))->first();
        $api_endpoint_update_status_scope      = ApiScope::where('name','=',sprintf('%s/api-endpoint/update.status',$current_realm))->first();
        $api_endpoint_add_scope_scope          = ApiScope::where('name','=',sprintf('%s/api-endpoint/add.scope',$current_realm))->first();
        $api_endpoint_remove_scope_scope       = ApiScope::where('name','=',sprintf('%s/api-endpoint/remove.scope',$current_realm))->first();

        $endpoint_api_endpoint_get                  = ApiEndpoint::where('name','=','get-api-endpoint')->first();
        $endpoint_api_endpoint_get->scopes()->attach($api_endpoint_read_scope->id);

        $endpoint_api_endpoint_get_page             = ApiEndpoint::where('name','=','api-endpoint-get-page')->first();
        $endpoint_api_endpoint_get_page->scopes()->attach($api_endpoint_read_scope->id);
        $endpoint_api_endpoint_get_page->scopes()->attach($api_endpoint_read_page_scope->id);

        $endpoint_api_endpoint_delete               = ApiEndpoint::where('name','=','delete-api-endpoint')->first();
        $endpoint_api_endpoint_delete->scopes()->attach($api_endpoint_delete_scope->id);

        $endpoint_api_endpoint_create               = ApiEndpoint::where('name','=','create-api-endpoint')->first();
        $endpoint_api_endpoint_create->scopes()->attach($api_endpoint_write_scope->id);

        $endpoint_api_endpoint_update       = ApiEndpoint::where('name','=','update-api-endpoint')->first();
        $endpoint_api_endpoint_update->scopes()->attach($api_endpoint_update_scope->id);

        $endpoint_api_add_api_endpoint_scope        = ApiEndpoint::where('name','=','add-api-endpoint-scope')->first();
        $endpoint_api_add_api_endpoint_scope->scopes()->attach($api_endpoint_write_scope->id);
        $endpoint_api_add_api_endpoint_scope->scopes()->attach($api_endpoint_add_scope_scope->id);

        $endpoint_api_remove_api_endpoint_scope        = ApiEndpoint::where('name','=','remove-api-endpoint-scope')->first();
        $endpoint_api_remove_api_endpoint_scope->scopes()->attach($api_endpoint_write_scope->id);
        $endpoint_api_remove_api_endpoint_scope->scopes()->attach($api_endpoint_remove_scope_scope->id);


        $endpoint_api_endpoint_update_status        = ApiEndpoint::where('name','=','update-api-endpoint-status')->first();
        $endpoint_api_endpoint_update_status->scopes()->attach($api_endpoint_update_scope->id);
        $endpoint_api_endpoint_update_status->scopes()->attach($api_endpoint_update_status_scope->id);

    }

    private function seedScopeEndpoints(){
        $api_scope                  = Api::where('name','=','api-scope')->first();
        $current_realm  = Config::get('app.url');
        // endpoints scopes

        ApiEndpoint::create(
            array(
                'name'            => 'get-scope',
                'active'          =>  true,
                'api_id'          => $api_scope->id,
                'route'           => 'api/v1/api-scope/{id}',
                'http_method'     => 'GET'
            )
        );


        ApiEndpoint::create(
            array(
                'name'            => 'delete-scope',
                'active'          =>  true,
                'api_id'          => $api_scope->id,
                'route'           => 'api/v1/api-scope/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'create-scope',
                'active'          =>  true,
                'api_id'          => $api_scope->id,
                'route'           => 'api/v1/api-scope',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-scope',
                'active'          =>  true,
                'api_id'          => $api_scope->id,
                'route'           => 'api/v1/api-scope',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-scope-status',
                'active'          =>  true,
                'api_id'          => $api_scope->id,
                'route'           => 'api/v1/api-scope/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'scope-get-page',
                'active'          =>  true,
                'api_id'          => $api_scope->id,
                'route'           => 'api/v1/api-scope/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );

        $api_scope_read_scope               = ApiScope::where('name','=',sprintf('%s/api-scope/read',$current_realm))->first();
        $api_scope_write_scope              = ApiScope::where('name','=',sprintf('%s/api-scope/write',$current_realm))->first();
        $api_scope_read_page_scope          = ApiScope::where('name','=',sprintf('%s/api-scope/read.page',$current_realm))->first();
        $api_scope_delete_scope             = ApiScope::where('name','=',sprintf('%s/api-scope/delete',$current_realm))->first();
        $api_scope_update_scope             = ApiScope::where('name','=',sprintf('%s/api-scope/update',$current_realm))->first();
        $api_scope_update_status_scope      = ApiScope::where('name','=',sprintf('%s/api-scope/update.status',$current_realm))->first();


        $endpoint_api_scope_get             = ApiEndpoint::where('name','=','get-scope')->first();
        $endpoint_api_scope_get->scopes()->attach($api_scope_read_scope->id);

        $endpoint_api_scope_get_page        = ApiEndpoint::where('name','=','scope-get-page')->first();
        $endpoint_api_scope_get_page->scopes()->attach($api_scope_read_scope->id);
        $endpoint_api_scope_get_page->scopes()->attach($api_scope_read_page_scope->id);

        $endpoint_api_scope_delete          = ApiEndpoint::where('name','=','delete-scope')->first();
        $endpoint_api_scope_delete->scopes()->attach($api_scope_delete_scope->id);

        $endpoint_api_scope_create          = ApiEndpoint::where('name','=','create-scope')->first();
        $endpoint_api_scope_create->scopes()->attach($api_scope_write_scope->id);

        $endpoint_api_scope_update               = ApiEndpoint::where('name','=','update-scope')->first();
        $endpoint_api_scope_update->scopes()->attach($api_scope_update_scope->id);

        $endpoint_api_scope_update_status        = ApiEndpoint::where('name','=','update-scope-status')->first();
        $endpoint_api_scope_update_status->scopes()->attach($api_scope_update_scope->id);
        $endpoint_api_scope_update_status->scopes()->attach($api_scope_update_status_scope->id);
    }
}