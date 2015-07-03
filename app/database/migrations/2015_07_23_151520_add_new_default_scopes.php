<?php

use Illuminate\Database\Migrations\Migration;
use oauth2\OAuth2Protocol;

class AddNewDefaultScopes extends Migration
{

    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        ApiScope::create(
            array(
                'name' => OAuth2Protocol::OpenIdConnect_Scope,
                'short_description' => 'OpenId Connect Protocol',
                'description' => 'OpenId Connect Protocol',
                'api_id' => null,
                'system' => true,
                'default' => true,
                'active' => true,
            )
        );

        ApiScope::create(
            array(
                'name' => OAuth2Protocol::OfflineAccess_Scope,
                'short_description' => 'allow to emit refresh tokens (offline access without user presence)',
                'description' => 'allow to emit refresh tokens (offline access without user presence)',
                'api_id' => null,
                'system' => true,
                'default' => true,
                'active' => true,
            )
        );
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        ApiScope::where('name', '=', OAuth2Protocol::OpenIdConnect_Scope)->first()-delete();
        ApiScope::where('name', '=', OAuth2Protocol::OfflineAccess_Scope)->first()-delete();
    }

}
