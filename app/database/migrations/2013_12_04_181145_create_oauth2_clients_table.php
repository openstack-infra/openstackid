<?php

use Illuminate\Database\Migrations\Migration;

class CreateOauth2ClientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('oauth2_client', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('app_name',255);
            $table->text('app_description');
            $table->string('app_logo',255);
            $table->string('client_id',255)->unique();
            $table->string('client_secret',255)->nullable();
            $table->smallInteger('client_type');
            $table->boolean('active')->default(true);
            $table->boolean('locked')->default(false);
            $table->bigInteger("user_id")->unsigned();
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('openid_users');
            $table->timestamps();

            $table->integer('max_auth_codes_issuance_qty')->default(0);
            $table->smallInteger('max_auth_codes_issuance_basis');

            $table->integer('max_access_token_issuance_qty')->default(0);
            $table->smallInteger('max_access_token_issuance_basis');

            $table->integer('max_refresh_token_issuance_qty')->default(0);
            $table->smallInteger('max_refresh_token_issuance_basis');

            $table->boolean('use_refresh_token')->default(true);
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('oauth2_client', function($table)
        {
            $table->dropForeign('user_id');
        });
        Schema::dropIfExists('oauth2_client');
	}

}