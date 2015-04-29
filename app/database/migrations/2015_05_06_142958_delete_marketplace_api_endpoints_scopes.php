<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteMarketplaceApiEndpointsScopes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $public_clouds  = Api::where('name','=','public-clouds')->first();
        if($public_clouds) $public_clouds->delete();
        $private_clouds = Api::where('name','=','private-clouds')->first();
        if($private_clouds) $private_clouds->delete();
        $consultants    = Api::where('name','=','consultants')->first();
        if($consultants) $consultants->delete();
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
