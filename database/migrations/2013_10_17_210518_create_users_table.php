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
            $table->string('identifier',255)->nullable();
            $table->string('external_id',100);
            $table->boolean('active')->default(true);
            $table->boolean('lock')->default(false);
            $table->boolean('public_profile_show_photo')->default(false);
            $table->boolean('public_profile_show_fullname')->default(false);
            $table->boolean('public_profile_show_email')->default(false);
            $table->dateTime('last_login_date')->default(date("Y-m-d H:i:s"));
            $table->integer('login_failed_attempt')->default(0);
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