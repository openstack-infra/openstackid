<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class UpdateUsersTableLaravel4126
 * http://laravel.com/docs/4.2/upgrade#upgrade-4.1.26
 */
class UpdateUsersTableLaravel4126 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('openid_users', function($table)
		{
			$table->string('remember_token',100)->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('openid_users', function($table)
		{
			$table->dropColumn('remember_token');
		});
	}

}

