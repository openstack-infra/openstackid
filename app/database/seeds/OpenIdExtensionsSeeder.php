<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/25/13
 * Time: 1:34 PM
 */

class OpenIdExtensionsSeeder extends Seeder {

    public function run()
    {
        DB::table('server_extensions')->delete();

        ServerExtension::create(
            array(
                'name'            => 'AX',
                'namespace'       => 'http://openid.net/srv/ax/1.0',
                'active'          => true,
                'extension_class' => 'openid\extensions\implementations\OpenIdAXExtension',
                'description'     => 'OpenID service extension for exchanging identity information between endpoints',
                'extension_class' => 'openid\extensions\implementations\OpenIdAXExtension',
                'view_name'       =>'extensions.ax',
            )
        );
    }

}
