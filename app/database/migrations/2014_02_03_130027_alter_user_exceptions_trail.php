<?php

use Illuminate\Database\Migrations\Migration;

class AlterUserExceptionsTrail extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('user_exceptions_trail', function($table)
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
        Schema::table('user_exceptions_trail', function($table)
        {
            $table->dropForeign('user_id');
            $table->dropColumn('user_id');
        });
	}

}