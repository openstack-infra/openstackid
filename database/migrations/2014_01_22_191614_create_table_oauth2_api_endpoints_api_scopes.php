<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableOauth2ApiEndpointsApiScopes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('oauth2_api_endpoint_api_scope', function($table)
        {
            $table->timestamps();

            $table->bigInteger("api_endpoint_id")->unsigned();
            $table->index('api_endpoint_id');
            $table->foreign('api_endpoint_id')
                ->references('id')
                ->on('oauth2_api_endpoint')
                ->onDelete('cascade')
                ->onUpdate('no action');;

            $table->bigInteger("scope_id")->unsigned();
            $table->index('scope_id');
            $table->foreign('scope_id')
                ->references('id')
                ->on('oauth2_api_scope')
                ->onDelete('cascade')
                ->onUpdate('no action');;
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('oauth2_api_endpoint_api_scope', function($table)
        {
            $table->dropForeign('api_endpoint_id');
        });

        Schema::table('oauth2_api_endpoint_api_scope', function($table)
        {
            $table->dropForeign('scope_id');
        });

        Schema::dropIfExists('oauth2_api_endpoints_api_scopes');
	}

}