<?php

use Illuminate\Database\Migrations\Migration;

class UpdateOauth2ApiEndpoint extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('oauth2_api_endpoint', function($table)
        {
            $table->boolean('allow_cors')->default(true);
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
            $table->dropColumn('allow_cors');
        });
	}
}