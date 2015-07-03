<?php

use Illuminate\Database\Migrations\Migration;
use oauth2\OAuth2Protocol;
use oauth2\models\IClient;

class UpdateOauth2ClientScopes extends Migration
{

    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        $clients       = Client::get();
        $scope_openid  = ApiScope::where('name', '=', OAuth2Protocol::OpenIdConnect_Scope)->first();
        $scope_offline = ApiScope::where('name', '=', OAuth2Protocol::OfflineAccess_Scope)->first();

        foreach($clients as $client)
        {
            $client->scopes()->attach($scope_openid->id);
            if($client->application_type === IClient::ApplicationType_Native || $client->application_type === IClient::ApplicationType_Web_App)
                $client->scopes()->attach($scope_offline->id);
        }
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        //
    }

}
