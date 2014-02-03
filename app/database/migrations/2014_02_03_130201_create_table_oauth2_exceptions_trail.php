<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableOauth2ExceptionsTrail extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('oauth2_exception_trail', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('from_ip',254);
            $table->string('exception_type',1024);
            $table->timestamps();
            $table->bigInteger("client_id")->unsigned()->nullable();
            $table->index('client_id');
            $table->foreign('client_id')
                ->references('id')
                ->on('oauth2_client')
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
        Schema::table('oauth2_exception_trail', function($table)
        {
            $table->dropForeign('client_id');
        });

        Schema::dropIfExists('oauth2_exception_trail');
	}

}