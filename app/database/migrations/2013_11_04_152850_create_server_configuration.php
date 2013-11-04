<?php

use Illuminate\Database\Migrations\Migration;

class CreateServerConfiguration extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('server_configuration', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('key',254);
            $table->string('value',1024);
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::dropIfExists('server_configuration');
	}

}