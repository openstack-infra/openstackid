<?php

use Illuminate\Database\Migrations\Migration;

class CreateOauth2ApiScope extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('oauth2_api_scope', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('name',512);
            $table->text('description');
            $table->boolean('active');
            $table->timestamps();

            $table->bigInteger("api_id")->unsigned();
            $table->index('api_id');
            $table->foreign('api_id')->references('id')->on('oauth2_api');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('oauth2_api_scope', function($table)
        {
            $table->dropForeign('api_id');
        });
        Schema::dropIfExists('oauth2_api_scope');
	}

}