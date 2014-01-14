<?php

class ApiScopeSeeder extends Seeder {

    public function run()
    {
        DB::table('oauth2_api_scope')->delete();

        $resource_server_api_create = Api::where('name','=','create resource server')->first();
        $resource_server_api_get = Api::where('name','=','get resource server')->first();
        $resource_server_api_get_page = Api::where('name','=','resource server get page')->first();
        $resource_server_api_regenerate = Api::where('name','=','resource server regenerate secret')->first();
        $resource_server_api_delete = Api::where('name','=','resource server delete')->first();
        $resource_server_api_update = Api::where('name','=','resource server update')->first();
        $resource_server_api_update_status = Api::where('name','=','resource server update status')->first();

        $current_realm = Config::get('app.url');


        // create api scopes

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/read',$current_realm),
                'short_description'  => 'Resource Server Read Access',
                'description'        => 'Resource Server Read Access',
                'api_id'             => $resource_server_api_get->id,
            )
        );


        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/read.page',$current_realm),
                'short_description'  => 'Resource Server Page Read Access',
                'description'        => 'Resource Server Page Read Access',
                'api_id'             => $resource_server_api_get_page->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/write',$current_realm),
                'short_description'  => 'Resource Server Write Access',
                'description'        => 'Resource Server Write Access',
                'api_id'             => $resource_server_api_create->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/delete',$current_realm),
                'short_description'  => 'Resource Server Delete Access',
                'description'        => 'Resource Server Delete Access',
                'api_id'             => $resource_server_api_delete->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/update',$current_realm),
                'short_description'  => 'Resource Server Update Access',
                'description'        => 'Resource Server Update Access',
                'api_id'             => $resource_server_api_update->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/update.status',$current_realm),
                'short_description'  => 'Resource Server Update Status',
                'description'        => 'Resource Server Update Status',
                'api_id'             => $resource_server_api_update_status->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/regenerate.secret',$current_realm),
                'short_description'  => 'Resource Server Regenerate Client Secret',
                'description'        => 'Resource Server Regenerate Client Secret',
                'api_id'             => $resource_server_api_regenerate->id,
            )
        );


    }

} 