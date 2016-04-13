<?php

use Illuminate\Database\Migrations\Migration;

class AlterBannedIpsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('banned_ips', function($table)
        {
            $table->timestamps();
            $table->string('exception_type',1024);
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
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('exception_type');
        });
	}

}