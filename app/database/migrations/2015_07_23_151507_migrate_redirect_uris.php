<?php

use Illuminate\Database\Migrations\Migration;

class MigrateRedirectUris extends Migration
{

    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        $uris = DB::table('oauth2_client_authorized_uri')->orderBy('client_id', 'desc')->get();
        foreach($uris as $uri)
        {
            $client = Client::find($uri->client_id);
            $redirect_uris = $client->redirect_uris;
            if(!empty($redirect_uris))
                $redirect_uris = $redirect_uris.','.$uri->uri;
            else
                $redirect_uris = $uri->uri;

            $client->redirect_uris = $redirect_uris;

            $client->save();
        }

        $uris = DB::table('oauth2_client_allowed_origin')->orderBy('client_id', 'desc')->get();
        foreach($uris as $uri)
        {
            $client = Client::find($uri->client_id);
            $allowed_origins = $client->allowed_origins;
            if(!empty($allowed_origins))
                $allowed_origins = $redirect_uris.','.$uri->allowed_origin;
            else
                $allowed_origins = $uri->allowed_origin;

            $client->allowed_origins = $allowed_origins;

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
