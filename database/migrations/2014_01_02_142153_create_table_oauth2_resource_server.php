<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableOauth2ResourceServer extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('oauth2_resource_server', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('friendly_name',255)->unique();
            $table->string('host',512);
            $table->string('ip',16);
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
        Schema::dropIfExists('oauth2_resource_server');
	}

}