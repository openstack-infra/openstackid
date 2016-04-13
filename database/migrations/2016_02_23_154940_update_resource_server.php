<?php

use Illuminate\Database\Migrations\Migration;

class UpdateResourceServer extends Migration
{

    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('oauth2_resource_server', function ($table) {
            $table->text('ips');
        });

        DB::statement("UPDATE oauth2_resource_server SET ips = ip;");

        Schema::table('oauth2_resource_server', function ($table) {
            $table->dropColumn('ip');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        //
    }

}
