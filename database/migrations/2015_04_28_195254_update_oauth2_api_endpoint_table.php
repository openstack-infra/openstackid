<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOauth2ApiEndpointTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('oauth2_api_endpoint', function($table)
        {

            $table->dropUnique('oauth2_api_endpoint_name_unique');
            $table->unique(array('name','http_method','api_id'));
        });
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
