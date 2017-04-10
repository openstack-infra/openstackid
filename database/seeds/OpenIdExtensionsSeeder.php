<?php

use Models\OpenId\ServerExtension;
use Illuminate\Database\Seeder;
use OpenId\Extensions\Implementations\OpenIdAXExtension;
use OpenId\Extensions\Implementations\OpenIdSREGExtension;
use OpenId\Extensions\Implementations\OpenIdOAuth2Extension;
use OpenId\Extensions\Implementations\OpenIdSREGExtension_1_0;

/**
 * Class OpenIdExtensionsSeeder
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
                'extension_class' => OpenIdAXExtension::class,
                'description'     => 'OpenID service extension for exchanging identity information between endpoints',
                'view_name'       =>'extensions.ax',
            )
        );

        ServerExtension::create(
            array(
                'name'            => 'SREG_1_0',
                'namespace'       => 'http://openid.net/sreg/1.0',
                'active'          => true,
                'extension_class' => OpenIdSREGExtension_1_0::class,
                'description'     => 'OpenID Simple Registration 1.0 is an extension to the OpenID Authentication protocol that allows for very light-weight profile exchange.',
                'view_name'       => 'extensions.sreg',
            )
        );

        ServerExtension::create(
            array(
                'name'            => 'SREG',
                'namespace'       => 'http://openid.net/extensions/sreg/1.1',
                'active'          => true,
                'extension_class' => OpenIdSREGExtension::class,
                'description'     => 'OpenID Simple Registration 1.1 is an extension to the OpenID Authentication protocol that allows for very light-weight profile exchange.',
                'view_name'       => 'extensions.sreg',
            )
        );

        ServerExtension::create(
            array(
                'name'            => 'OAUTH2',
                'namespace'       => 'http://specs.openid.net/extensions/oauth/2.0',
                'active'          => true,
                'extension_class' => OpenIdOAuth2Extension::class,
                'description'     => 'The OpenID OAuth2 Extension describes how to make the OpenID Authentication and OAuth2 Core specifications work well together.',
                'view_name'       => 'extensions.oauth2',
            )
        );
    }

}
