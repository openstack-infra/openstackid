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
            $table->string('name',100)->nullable()->default('');
            $table->string('namespace',255)->nullable()->default('');
            $table->boolean('active')->default(false);
            $table->string('extension_class',255)->nullable()->default('');
            $table->string('description',255)->nullable()->default('');
            $table->string('view_name',255)->nullable()->default('');
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