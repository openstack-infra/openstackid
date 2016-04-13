<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableOauth2ApiEndpoints extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('oauth2_api_endpoint', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->boolean('active')->default(true);
            $table->text('description')->nullable();
            $table->string('name',255)->unique();
            $table->timestamps();
            $table->text("route");
            $table->enum('http_method', array('GET', 'HEAD','POST','PUT','DELETE','TRACE','CONNECT','OPTIONS','PATCH'));
            $table->bigInteger("api_id")->unsigned();
            $table->index('api_id');

            $table->foreign('api_id')
                ->references('id')
                ->on('oauth2_api')
                ->onDelete('cascade')
                ->onUpdate('no action');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('oauth2_api_endpoints', function($table)
        {
            $table->dropForeign('api_id');
        });

        Schema::dropIfExists('oauth2_api_endpoints');
	}

}