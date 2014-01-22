<?php

class ApiSeeder extends Seeder {
    public function run()
    {
        DB::table('oauth2_api')->delete();

        $resource_server = ResourceServer::first();
        //create api endpoints

        //resource server api

        Api::create(
            array(
                'name'            => 'create resource server',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server',
                'http_method'     => 'POST'
            )
        );

        Api::create(
            array(
                'name'            => 'get resource server',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server/{id}',
                'http_method'     => 'GET'
            )
        );

        Api::create(
            array(
                'name'            => 'resource server regenerate secret',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server/regenerate-client-secret/{id}',
                'http_method'     => 'GET'
            )
        );

        Api::create(
            array(
                'name'            => 'resource server get page',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );

        Api::create(
            array(
                'name'            => 'resource server delete',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server/{id}',
                'http_method'     => 'DELETE'
            )
        );

        Api::create(
            array(
                'name'            => 'resource server update',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server',
                'http_method'     => 'PUT'
            )
        );

        Api::create(
            array(
                'name'            => 'resource server update status',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );




        // endpoints api

        Api::create(
            array(
                'name'            => 'get endpoint',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/api-endpoints/{id}',
                'http_method'     => 'GET'
            )
        );


        Api::create(
            array(
                'name'            => 'delete endpoint',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/api-endpoints/{id}',
                'http_method'     => 'DELETE'
            )
        );

        Api::create(
            array(
                'name'            => 'create endpoint',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/api-endpoints',
                'http_method'     => 'POST'
            )
        );

        Api::create(
            array(
                'name'            => 'update endpoint',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/api-endpoints',
                'http_method'     => 'PUT'
            )
        );

        Api::create(
            array(
                'name'            => 'update endpoint status',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/api-endpoints/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        Api::create(
            array(
                'name'            => 'endpoint get page',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/api-endpoints/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );


    }

} 