<?php

class ApiSeeder extends Seeder {
    public function run()
    {
        DB::table('oauth2_api_endpoint_api_scope')->delete();
        DB::table('oauth2_client_api_scope')->delete();
        DB::table('oauth2_api_scope')->delete();
        DB::table('oauth2_api')->delete();

        $resource_server = ResourceServer::first();

        Api::create(
            array(
                'name'               => 'resource-server',
                'logo'               =>  null,
                'active'             =>  true,
                'Description'        => 'Resource Server CRUD operations',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'api',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Api CRUD operations',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
            )
        );


        Api::create(
            array(
                'name'            => 'api-endpoint',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Api Endpoints CRUD operations',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'api-scope',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Api Scopes CRUD operations',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
            )
        );

    }

} 