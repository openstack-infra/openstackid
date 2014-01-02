<?php

class ResourceServerSeeder extends Seeder {
    public function run()
    {
        DB::table('oauth2_resource_server')->delete();

        ResourceServer::create(
            array(
                'friendly_name'   => 'test resource server',
                'host'            => 'https://www.resource.test1.com',
                'ip'              => '127.0.0.1'
            )
        );

    }

} 