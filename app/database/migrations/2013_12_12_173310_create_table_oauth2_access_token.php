<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableOauth2AccessToken extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('oauth2_access_token', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('value',255)->unique();
            $table->string('from_ip',255);
            $table->string('associated_authorization_code',255)->nullable();
            $table->integer('lifetime');
            $table->text('scope');
            $table->text('audience');
            $table->timestamps();

            $table->bigInteger("client_id")->unsigned();
            $table->index('client_id');
            $table->foreign('client_id')->references('id')->on('oauth2_client');

            $table->bigInteger("refresh_token_id")->unsigned()->nullable();
            $table->index('refresh_token_id');
            $table->foreign('refresh_token_id')->references('id')->on('oauth2_refresh_token');
        });
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('oauth2_access_token', function($table)
        {
            $table->dropForeign('client_id');
            $table->dropForeign('refresh_token_id');
        });
        Schema::dropIfExists('oauth2_access_token');
	}

}