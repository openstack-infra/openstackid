<?php

use Illuminate\Database\Migrations\Migration;

class CreateUserActivityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('user_actions', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('from_ip',254);
            $table->string('user_action',512);
            $table->bigInteger("user_id")->unsigned();
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('openid_users');
            $table->timestamps();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('user_actions', function($table)
        {
            $table->dropForeign('user_id');
        });
        Schema::dropIfExists('user_actions');
	}

}