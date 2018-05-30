<?php
use Models\OAuth2\ResourceServer;
use Illuminate\Database\Seeder;

/**
 * Class ResourceServerSeeder
 */
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
                'ips'              => '127.0.0.1'
            )
        );
    }
}