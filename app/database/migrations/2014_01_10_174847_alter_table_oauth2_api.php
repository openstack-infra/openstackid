<?php

use Illuminate\Database\Migrations\Migration;

class AlterTableOauth2Api extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('oauth2_api', function($table)
        {
            $table->text("route");
            $table->enum('http_method', array('GET', 'HEAD','POST','PUT','DELETE','TRACE','CONNECT','OPTIONS'));
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('oauth2_api', function($table)
        {
            $table->dropColumn('route');
            $table->dropColumn('http_method');
        });
	}

}