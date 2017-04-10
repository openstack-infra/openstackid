<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use OpenId\Extensions\Implementations\OpenIdSREGExtension_1_0;
use Models\OpenId\ServerExtension;

/**
 * Class UpdateServerExtOpenidSreg10
 */
class UpdateServerExtOpenidSreg10 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
