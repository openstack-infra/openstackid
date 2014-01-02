<?php

class ApiSeeder extends Seeder {
    public function run()
    {
        DB::table('oauth2_api')->delete();

        $resource_server = ResourceServer::first();

        Api::create(
            array(
                'name'               => 'api user activities',
                'logo'               =>  null,
                'active'             =>  true,
                'resource_server_id' => $resource_server->id
            )
        );

        Api::create(
            array(
                'name'               => 'api echo-sign',
                'logo'               =>  null,
                'active'             =>  true,
                'resource_server_id' => $resource_server->id
            )
        );

    }

} 