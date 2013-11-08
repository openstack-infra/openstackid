<?php

use Illuminate\Database\Migrations\Migration;

class AlterUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('openid_users', function($table)
        {
            $table->unique('external_id');
            $table->unique('identifier');
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
            $table->dropUnique('external_id');
            $table->dropUnique('identifier');
        });
	}

}