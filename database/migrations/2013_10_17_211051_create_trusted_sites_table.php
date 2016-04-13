<?php

use Illuminate\Database\Migrations\Migration;

class CreateTrustedSitesTable extends Migration {

    public function up()
    {
        Schema::create('openid_trusted_sites', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->string('realm',1024);
            $table->text('data')->nullable();
            $table->string('policy',255);
            $table->bigInteger("user_id")->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('openid_trusted_sites');
    }
}