<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTableUserExceptionsTrail extends Migration {

	/**
	 * Run the migrations.
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_exceptions_trail', function ($table) {
			$table->longText('stack_trace')->nullable();
		});
}

	/**
	 * Reverse the migrations.
	 * @return void
	 */
	public function down()
	{
		//
	}

}
