<?php

use Illuminate\Database\Migrations\Migration;

class CreateOauth2ClientApiScope extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('oauth2_client_api_scope', function($table)
        {
            $table->timestamps();

            $table->bigInteger("client_id")->unsigned();
            $table->index('client_id');
            $table->foreign('client_id')->references('id')->on('oauth2_client');

            $table->bigInteger("scope_id")->unsigned();
            $table->index('scope_id');
            $table->foreign('scope_id')->references('id')->on('oauth2_api_scope');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('oauth2_client_api_scope', function($table)
        {
            $table->dropForeign('client_id');
        });

        Schema::table('oauth2_client_api_scope', function($table)
        {
            $table->dropForeign('scope_id');
        });
        Schema::dropIfExists('oauth2_client_api_scope');
	}

}