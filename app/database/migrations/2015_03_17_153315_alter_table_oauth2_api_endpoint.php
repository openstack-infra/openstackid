<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableOauth2ApiEndpoint extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('oauth2_api_endpoint', function($table)
		{
			$table->bigInteger("rate_limit")->unsigned()->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

		Schema::table('oauth2_api_endpoint', function($table)
		{
			$table->dropColumn('rate_limit');
		});
	}

}
