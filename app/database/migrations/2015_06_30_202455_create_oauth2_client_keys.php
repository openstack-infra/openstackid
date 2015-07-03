<?php

use Illuminate\Database\Migrations\Migration;
use jwk\JSONWebKeyTypes;
use jwk\JSONWebKeyPublicKeyUseValues;

/**
 * Class CreateOauth2ClientKeys
 */
class CreateOauth2ClientKeys extends Migration
{

    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('oauth2_client_keys', function($table)
        {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('pem_content');
            $table->enum('usage', JSONWebKeyPublicKeyUseValues::$valid_uses )->default(JSONWebKeyPublicKeyUseValues::Signature);
            $table->enum('type', JSONWebKeyTypes::$valid_keys_set )->default(JSONWebKeyTypes::RSA);
            // FK
            $table->bigInteger("oauth2_client_id")->unsigned();
            $table->index('oauth2_client_id');
            $table->foreign('oauth2_client_id')
                ->references('id')
                ->on('oauth2_client')
                ->onDelete('cascade')
                ->onUpdate('no action');;
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('oauth2_client', function($table)
        {
            $table->dropForeign('oauth2_client_id');
        });
        Schema::dropIfExists('openid_users');
    }

}
