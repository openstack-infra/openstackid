<?php

use Illuminate\Database\Migrations\Migration;

class AlterTrustedSitesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('openid_trusted_sites', function($table)
        {
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('openid_users');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('openid_trusted_sites', function($table)
        {
            $table->dropForeign('user_id');
        });
	}
}