<?php

use Illuminate\Database\Migrations\Migration;

class CreateAssociationsTable extends Migration {

    public function up()
    {
        Schema::create('openid_associations', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('identifier',255);
            $table->string('mac_function',255);
            $table->binary('secret');
            $table->string('realm',1024)->nullable();
            $table->smallInteger('type');
            $table->integer('lifetime')->unsigned();
            $table->dateTime('issued');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('openid_associations');
    }

}