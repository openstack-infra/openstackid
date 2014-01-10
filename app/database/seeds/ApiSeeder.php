<?php

class ApiSeeder extends Seeder {
    public function run()
    {
        DB::table('oauth2_api')->delete();

        $resource_server = ResourceServer::first();

        Api::create(
            array(
                'name'               => 'get resource server api',
                'logo'               =>  null,
                'active'             =>  true,
                'resource_server_id' => $resource_server->id,
                'route'              => '/api/v1/resource-server/{id}',
                'http_method'        => 'GET'
            )
        );

    }

} 