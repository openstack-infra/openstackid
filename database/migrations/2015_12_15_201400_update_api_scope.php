<?php

use Illuminate\Database\Migrations\Migration;

class UpdateApiScope extends Migration
{

    public function up()
    {
        Schema::table('oauth2_api_scope', function ($table) {
            $table->boolean('assigned_by_groups')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('oauth2_api_scope', function ($table) {
            $table->dropColumn('assigned_by_groups');
        });
    }

}
