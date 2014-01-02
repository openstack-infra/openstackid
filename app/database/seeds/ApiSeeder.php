<?php

class ApiSeeder extends Seeder {
    public function run()
    {
        DB::table('oauth2_api')->delete();

        $resource_server = ResourceServer::first();
        Api::create(
            array(
                'name'               => 'test api user activities',
                'logo'               =>  null,
                'active'             =>  true,
                'resource_server_id' => $resource_server->id
            )
        );

    }

} 