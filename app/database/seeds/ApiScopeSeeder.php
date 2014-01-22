<?php

class ApiScopeSeeder extends Seeder {

    public function run()
    {
        DB::table('oauth2_api_scope')->delete();

        $current_realm = Config::get('app.url');

        //resource server api
        $resource_server_api_create        = Api::where('name','=','create resource server')->first();
        $resource_server_api_get           = Api::where('name','=','get resource server')->first();
        $resource_server_api_get_page      = Api::where('name','=','resource server get page')->first();
        $resource_server_api_regenerate    = Api::where('name','=','resource server regenerate secret')->first();
        $resource_server_api_delete        = Api::where('name','=','resource server delete')->first();
        $resource_server_api_update        = Api::where('name','=','resource server update')->first();
        $resource_server_api_update_status = Api::where('name','=','resource server update status')->first();

        //endpoint api
        $endpoint_api_get                  = Api::where('name','=','get endpoint')->first();
        $endpoint_api_get_page             = Api::where('name','=','endpoint get page')->first();
        $endpoint_api_delete               = Api::where('name','=','delete endpoint')->first();
        $endpoint_api_create               = Api::where('name','=','create endpoint')->first();
        $endpoint_api_update               = Api::where('name','=','update endpoint')->first();
        $endpoint_api_update_status        = Api::where('name','=','update endpoint status')->first();

        // create api scopes

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/read',$current_realm),
                'short_description'  => 'Resource Server Read Access',
                'description'        => 'Resource Server Read Access',
                'api_id'             => $resource_server_api_get->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/read.page',$current_realm),
                'short_description'  => 'Resource Server Page Read Access',
                'description'        => 'Resource Server Page Read Access',
                'api_id'             => $resource_server_api_get_page->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/write',$current_realm),
                'short_description'  => 'Resource Server Write Access',
                'description'        => 'Resource Server Write Access',
                'api_id'             => $resource_server_api_create->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/delete',$current_realm),
                'short_description'  => 'Resource Server Delete Access',
                'description'        => 'Resource Server Delete Access',
                'api_id'             => $resource_server_api_delete->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/update',$current_realm),
                'short_description'  => 'Resource Server Update Access',
                'description'        => 'Resource Server Update Access',
                'api_id'             => $resource_server_api_update->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/update.status',$current_realm),
                'short_description'  => 'Resource Server Update Status',
                'description'        => 'Resource Server Update Status',
                'api_id'             => $resource_server_api_update_status->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/regenerate.secret',$current_realm),
                'short_description'  => 'Resource Server Regenerate Client Secret',
                'description'        => 'Resource Server Regenerate Client Secret',
                'api_id'             => $resource_server_api_regenerate->id,
                'system'             => true,
            )
        );

        // api endpoint scopes


        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/api-endpoints/read',$current_realm),
                'short_description'  => 'Get Api Endpoint',
                'description'        => 'Get Api Endpoint',
                'api_id'             => $endpoint_api_get->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/api-endpoints/delete',$current_realm),
                'short_description'  => 'Deletes Api Endpoint',
                'description'        => 'Deletes Api Endpoint',
                'api_id'             => $endpoint_api_delete->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/api-endpoints/write',$current_realm),
                'short_description'  => 'Create Api Endpoint',
                'description'        => 'Create Api Endpoint',
                'api_id'             => $endpoint_api_create->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/api-endpoints/update',$current_realm),
                'short_description'  => 'Update Api Endpoint',
                'description'        => 'Update Api Endpoint',
                'api_id'             => $endpoint_api_update->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/api-endpoints/update.status',$current_realm),
                'short_description'  => 'Update Api Endpoint Status',
                'description'        => 'Update Api Endpoint Status',
                'api_id'             => $endpoint_api_update_status->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/api-endpoints/read.page',$current_realm),
                'short_description'  => 'Get Api Endpoints By Page',
                'description'        => 'Get Api Endpoints By Page',
                'api_id'             => $endpoint_api_get_page->id,
                'system'             => true,
            )
        );


    }

} 