<?php

use Illuminate\Database\Migrations\Migration;

class CreateOauth2ClientsAuthorizedUris extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('oauth2_client_authorized_uri', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('uri',255);

            $table->bigInteger("client_id")->unsigned();
            $table->index('client_id');
            $table->foreign('client_id')->references('id')->on('oauth2_client');

            $table->timestamps();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('oauth2_client_authorized_uri', function($table)
        {
            $table->dropForeign('client_id');
        });
        Schema::dropIfExists('oauth2_client_authorized_uri');
	}

}