<?php

use Illuminate\Database\Migrations\Migration;

class AlterTableOauth2Client extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('oauth2_client', function($table)
        {
            $table->bigInteger("resource_server_id")->unsigned()->nullable();
            $table->index('resource_server_id');
            $table->foreign('resource_server_id')->references('id')->on('oauth2_resource_server');
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
            $table->dropForeign('resource_server_id');
            $table->dropColumn('resource_server_id');
        });
	}

}