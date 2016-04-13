<?php

use Illuminate\Database\Migrations\Migration;

class CreateTableOauth2UserConsents extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('oauth2_user_consents', function($table)
        {
            $table->bigIncrements('id')->unsigned();
            $table->text('scopes');

            $table->bigInteger("client_id")->unsigned();
            $table->index('client_id');
            $table->foreign('client_id')
                ->references('id')
                ->on('oauth2_client')
                ->onDelete('cascade')
                ->onUpdate('no action');

            $table->bigInteger("user_id")->unsigned();
            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('openid_users')
                ->onDelete('cascade')
                ->onUpdate('no action');

            $table->timestamps();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('oauth2_user_consents', function($table)
        {
            $table->dropForeign('client_id');
            $table->dropForeign('openid_users');
        });

        Schema::dropIfExists('oauth2_user_consents');
    }

}