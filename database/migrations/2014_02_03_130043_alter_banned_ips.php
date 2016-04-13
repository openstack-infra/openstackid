<?php

use Illuminate\Database\Migrations\Migration;

class AlterBannedIps extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('banned_ips', function($table)
        {
            $table->bigInteger("user_id")->unsigned()->nullable();
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
        Schema::table('banned_ips', function($table)
        {
            $table->dropForeign('user_id');
            $table->dropColumn('user_id');
        });
	}

}