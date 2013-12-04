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
            $table->string('app_name',255)->unique();
            $table->text('app_description');
            $table->string('app_logo',255);
            $table->string('client_id',32)->unique();
            $table->string('client_secret',64)->unique();
            $table->smallInteger('client_type');
            $table->boolean('active');
            $table->bigInteger("user_id")->unsigned();
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('openid_users');
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
        Schema::table('oauth2_client', function($table)
        {
            $table->dropForeign('user_id');
        });
        Schema::dropIfExists('oauth2_client');
	}

}