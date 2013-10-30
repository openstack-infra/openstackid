<?php

use Illuminate\Database\Migrations\Migration;

class CreateBannedIps extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('banned_ips', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('ip',1024);
            $table->bigInteger("hits")->unsigned()->default(1);
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::dropIfExists('banned_ips');
	}

}