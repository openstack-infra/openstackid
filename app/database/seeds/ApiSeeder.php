<?php

/**
 * Class ApiSeeder
 */
class ApiSeeder extends Seeder {

    public function run()
    {
        DB::table('oauth2_api_endpoint_api_scope')->delete();
        DB::table('oauth2_client_api_scope')->delete();
        DB::table('oauth2_api_scope')->delete();
        DB::table('oauth2_api')->delete();

        $resource_server = ResourceServer::first();

        // users
        Api::create(
            array(
                'name'            => 'users',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'User Info',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
            )
        );
        // public clouds
        Api::create(
            array(
                'name'            => 'public-clouds',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Marketplace Public Clouds',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
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
                'logo'               => asset('img/apis/server.png')
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
                'logo'               => asset('img/apis/server.png')
            )
        );
    }
}