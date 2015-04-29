<?php

class OpenIdExtensionsSeeder extends Seeder {

    public function run()
    {
        DB::table('server_extensions')->delete();

        ServerExtension::create(
            array(
                'name'            => 'AX',
                'namespace'       => 'http://openid.net/srv/ax/1.0',
                'active'          => false,
                'extension_class' => 'openid\extensions\implementations\OpenIdAXExtension',
                'description'     => 'OpenID service extension for exchanging identity information between endpoints',
                'view_name'       =>'extensions.ax',
            )
        );

        ServerExtension::create(
            array(
                'name'            => 'SREG',
                'namespace'       => 'http://openid.net/extensions/sreg/1.1',
                'active'          => true,
                'extension_class' => 'openid\extensions\implementations\OpenIdSREGExtension',
                'description'     => 'OpenID Simple Registration is an extension to the OpenID Authentication protocol that allows for very light-weight profile exchange.',
                'view_name'       => 'extensions.sreg',
            )
        );


        ServerExtension::create(
            array(
                'name'            => 'OAUTH2',
                'namespace'       => 'http://specs.openid.net/extensions/oauth/2.0',
                'active'          => true,
                'extension_class' => 'openid\extensions\implementations\OpenIdOAuth2Extension',
                'description'     => 'The OpenID OAuth2 Extension describes how to make the OpenID Authentication and OAuth2 Core specifications work well together.',
                'view_name'       => 'extensions.oauth2',
            )
        );
    }

}
