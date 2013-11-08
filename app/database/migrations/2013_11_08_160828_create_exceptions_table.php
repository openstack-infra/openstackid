<?php

use Illuminate\Database\Migrations\Migration;

class CreateExceptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('user_exceptions_trail', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('from_ip',254);
            $table->string('exception_type',1024);
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
        Schema::dropIfExists('user_exceptions_trail');
	}

}