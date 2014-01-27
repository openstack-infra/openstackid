<?php

class ApiScopeSeeder extends Seeder {


    public function run()
    {
        DB::table('oauth2_api_endpoint_api_scope')->delete();
        DB::table('oauth2_client_api_scope')->delete();
        DB::table('oauth2_api_scope')->delete();

        $this->seedResourceServerScopes();
        $this->seedApiScopes();
        $this->seedApiEndpointScopes();
        $this->seedApiScopeScopes();
    }

    private function seedResourceServerScopes(){

        $resource_server        = Api::where('name','=','resource-server')->first();
        $current_realm          = Config::get('app.url');

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/read',$current_realm),
                'short_description'  => 'Resource Server Read Access',
                'description'        => 'Resource Server Read Access',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/read.page',$current_realm),
                'short_description'  => 'Resource Server Page Read Access',
                'description'        => 'Resource Server Page Read Access',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/write',$current_realm),
                'short_description'  => 'Resource Server Write Access',
                'description'        => 'Resource Server Write Access',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/delete',$current_realm),
                'short_description'  => 'Resource Server Delete Access',
                'description'        => 'Resource Server Delete Access',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/update',$current_realm),
                'short_description'  => 'Resource Server Update Access',
                'description'        => 'Resource Server Update Access',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/update.status',$current_realm),
                'short_description'  => 'Resource Server Update Status',
                'description'        => 'Resource Server Update Status',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/regenerate.secret',$current_realm),
                'short_description'  => 'Resource Server Regenerate Client Secret',
                'description'        => 'Resource Server Regenerate Client Secret',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

    }

    private function seedApiScopes(){
        $api           = Api::where('name','=','api')->first();
        $current_realm = Config::get('app.url');

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/read',$current_realm),
                'short_description'  => 'Get Api',
                'description'        => 'Get Api',
                'api_id'             => $api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/delete',$current_realm),
                'short_description'  => 'Deletes Api',
                'description'        => 'Deletes Api',
                'api_id'             => $api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/write',$current_realm),
                'short_description'  => 'Create Api',
                'description'        => 'Create Api',
                'api_id'             => $api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/update',$current_realm),
                'short_description'  => 'Update Api',
                'description'        => 'Update Api',
                'api_id'             => $api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/update.status',$current_realm),
                'short_description'  => 'Update Api Status',
                'description'        => 'Update Api Status',
                'api_id'             => $api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/read.page',$current_realm),
                'short_description'  => 'Get Api By Page',
                'description'        => 'Get Api By Page',
                'api_id'             => $api->id,
                'system'             => true,
            )
        );

    }

    private function seedApiEndpointScopes(){
        $api_endpoint  = Api::where('name','=','api-endpoint')->first();
        $current_realm = Config::get('app.url');

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/read',$current_realm),
                'short_description'  => 'Get Api Endpoint',
                'description'        => 'Get Api Endpoint',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/delete',$current_realm),
                'short_description'  => 'Deletes Api Endpoint',
                'description'        => 'Deletes Api Endpoint',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/write',$current_realm),
                'short_description'  => 'Create Api Endpoint',
                'description'        => 'Create Api Endpoint',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/update',$current_realm),
                'short_description'  => 'Update Api Endpoint',
                'description'        => 'Update Api Endpoint',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/update.status',$current_realm),
                'short_description'  => 'Update Api Endpoint Status',
                'description'        => 'Update Api Endpoint Status',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/read.page',$current_realm),
                'short_description'  => 'Get Api Endpoints By Page',
                'description'        => 'Get Api Endpoints By Page',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );


        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/add.scope',$current_realm),
                'short_description'  => 'Add required scope to endpoint',
                'description'        => 'Add required scope to endpoint',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );


        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/remove.scope',$current_realm),
                'short_description'  => 'Remove required scope to endpoint',
                'description'        => 'Remove required scope to endpoint',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );

    }

    private function seedApiScopeScopes(){
        $current_realm = Config::get('app.url');
        $api_scope              = Api::where('name','=','api-scope')->first();

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-scope/read',$current_realm),
                'short_description'  => 'Get Api Scope',
                'description'        => 'Get Api Scope',
                'api_id'             => $api_scope->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-scope/delete',$current_realm),
                'short_description'  => 'Deletes Api Scope',
                'description'        => 'Deletes Api Scope',
                'api_id'             => $api_scope->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-scope/write',$current_realm),
                'short_description'  => 'Create Api Scope',
                'description'        => 'Create Api Scope',
                'api_id'             => $api_scope->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-scope/update',$current_realm),
                'short_description'  => 'Update Api Scope',
                'description'        => 'Update Api Scope',
                'api_id'             => $api_scope->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-scope/update.status',$current_realm),
                'short_description'  => 'Update Api Scope Status',
                'description'        => 'Update Api Scope Status',
                'api_id'             => $api_scope->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-scope/read.page',$current_realm),
                'short_description'  => 'Get Api Scopes By Page',
                'description'        => 'Get Api Scopes By Page',
                'api_id'             => $api_scope->id,
                'system'             => true,
            )
        );

    }
}