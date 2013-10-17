<?php

use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('openid_users', function($table)
        {
            $table->bigIncrements('id');
            $table->string('identifier',255);
            $table->string('external_id',100);
            $table->boolean('active');
            $table->dateTime('last_login_date');
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
        Schema::dropIfExists('openid_users');
	}

}