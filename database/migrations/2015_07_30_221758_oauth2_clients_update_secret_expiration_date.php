<?php

use Illuminate\Database\Migrations\Migration;
use OAuth2\Models\IClient;
use Models\OAuth2\Client;

class Oauth2ClientsUpdateSecretExpirationDate extends Migration
{

    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        $clients = Client::get();
        $now     = new \DateTime();

        foreach ($clients as $client)
        {
            if ($client->client_type !== IClient::ClientType_Confidential) continue;
            // default 6 months
            $client->client_secret_expires_at = $now->add(new \DateInterval('P6M'));
            $client->save();
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
