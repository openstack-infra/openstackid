<?php

use Models\OAuth2\ResourceServer;
use Models\OAuth2\Api;
use Illuminate\Database\Seeder;
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
                'name'               => 'users',
                'active'             =>  true,
                'Description'        => 'User Info',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('/assets/img/apis/server.png')
            )
        );
    }
}