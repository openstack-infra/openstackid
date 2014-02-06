<?php

class ResourceServerSeeder extends Seeder {

    public function run()
    {
        DB::table('oauth2_resource_server')->delete();
        $current_realm = Config::get('app.url');

        $res = @parse_url($current_realm);

        ResourceServer::create(
            array(
                'friendly_name'   => 'openstack id server',
                'host'            =>  $res['host'],
                'ip'              => '127.0.0.1'
            )
        );
    }
}