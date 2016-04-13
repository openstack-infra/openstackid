<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOauthAppAdmins extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('oauth2_client_admin_users', function($table)
		{
			$table->timestamps();

			$table->bigInteger("oauth2_client_id")->unsigned();
			$table->index('oauth2_client_id');
			$table->foreign('oauth2_client_id')
					->references('id')
					->on('oauth2_client')
					->onDelete('cascade')
					->onUpdate('no action'); ;

			$table->bigInteger("user_id")->unsigned();
			$table->index('user_id');
			$table->foreign('user_id')
					->references('id')
					->on('openid_users')
					->onDelete('cascade')
					->onUpdate('no action');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('oauth2_client_admin_users');
	}

}
