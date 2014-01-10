<?php

class ResourceServerSeeder extends Seeder {

    public function run()
    {
        DB::table('oauth2_resource_server')->delete();
        $current_realm = Config::get('app.url');
        ResourceServer::create(
            array(
                'friendly_name'   => 'openstack id server',
                'host'            => $current_realm,
                'ip'              => '127.0.0.1'
            )
        );

    }

} 