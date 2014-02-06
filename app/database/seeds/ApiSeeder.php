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
                'name'            => 'users',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'User Info',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
            )
        );
    }
}