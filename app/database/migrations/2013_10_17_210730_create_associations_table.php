<?php

use Illuminate\Database\Migrations\Migration;

class CreateAssociationsTable extends Migration {

    public function up()
    {
        Schema::create('openid_associations', function($table)
        {
            $table->bigIncrements('id');
            $table->string('identifier',255);
            $table->string('mac_function',100);
            $table->string('secret',1024);
            $table->smallInteger('type');
            $table->integer('lifetime');
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