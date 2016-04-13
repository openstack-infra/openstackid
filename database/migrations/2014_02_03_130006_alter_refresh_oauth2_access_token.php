<?php

use Illuminate\Database\Migrations\Migration;

class AlterRefreshOauth2AccessToken extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('oauth2_refresh_token', function($table)
        {
            $table->bigInteger("user_id")->unsigned()->nullable();
            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('openid_users')
                ->onDelete('cascade')
                ->onUpdate('no action');;
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('oauth2_refresh_token', function($table)
        {
            $table->dropForeign('user_id');
            $table->dropColumn('user_id');
        });
	}

}