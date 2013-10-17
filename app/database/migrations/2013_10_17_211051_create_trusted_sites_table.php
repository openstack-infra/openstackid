<?php

use Illuminate\Database\Migrations\Migration;

class CreateTrustedSitesTable extends Migration {

    public function up()
    {
        Schema::create('openid_trusted_sites', function($table)
        {
            $table->bigIncrements('id');
            $table->string('realm',255);
            $table->string('data',1024);
            $table->string('policy',100);
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