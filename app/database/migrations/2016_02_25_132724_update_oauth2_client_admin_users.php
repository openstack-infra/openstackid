<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOauth2ClientAdminUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('oauth2_client', function($table) {
			$table->bigInteger('edited_by_id')->unsigned()->nullable();
			$table->index('edited_by_id');
			$table->foreign('edited_by_id')->references('id')->on('openid_users');
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
			$table->dropForeign('edited_by_id');
			$table->dropIndex('edited_by_id');
			$table->dropColumn('edited_by_id');
		});
	}

}
