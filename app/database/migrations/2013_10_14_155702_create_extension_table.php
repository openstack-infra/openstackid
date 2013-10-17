<?php

use Illuminate\Database\Migrations\Migration;

class CreateExtensionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('server_extensions', function($table)
        {
            $table->bigIncrements('id');
            $table->string('name',100);
            $table->string('namespace',255);
            $table->boolean('active');
            $table->string('extension_class',255);
            $table->string('description',255);
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
        Schema::dropIfExists('server_extensions');
	}

}