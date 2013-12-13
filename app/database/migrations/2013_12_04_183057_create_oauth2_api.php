<?php

use Illuminate\Database\Migrations\Migration;

class CreateOauth2Api extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('oauth2_api', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('name',255)->unique();
            $table->string('endpoint_url',512);
            $table->string('logo',255)->nullable();
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('oauth2_api');
	}

}