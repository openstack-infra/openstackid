<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOpenidUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('openid_users', function($table)
        {
            $table->bigInteger("external_identifier")->unsigned()->nullable();
            $table->unique('external_identifier');
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
            $table->dropUnique('external_identifier');
            $table->dropColumn('external_identifier');
        });
	}

}
