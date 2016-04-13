<?php

use Illuminate\Database\Migrations\Migration;

class UpdateOauth2Client extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('oauth2_client', function($table)
        {
            $table->text("website")->nullable();
            $table->enum('application_type', array('WEB_APPLICATION', 'JS_CLIENT','SERVICE'));
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
            $table->dropColumn('website');
            $table->dropColumn('application_type');
        });
	}
}