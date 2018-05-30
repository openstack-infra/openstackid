<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameTableOauth2AssymetricKeys extends Migration
{
    /**
     * Run the migrations.
     *N
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('oauth2_assymetric_keys'))
            Schema::rename("oauth2_assymetric_keys", "oauth2_asymmetric_keys");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
