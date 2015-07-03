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
        Schema::create('oauth2_client_public_keys', function($table)
        {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->text('pem_content');
            $table->string('kid');
            $table->boolean('active')->default(true);
            $table->enum('usage', JSONWebKeyPublicKeyUseValues::$valid_uses )->default(JSONWebKeyPublicKeyUseValues::Signature);
            $table->enum('type', array(JSONWebKeyTypes::RSA, JSONWebKeyTypes::EllipticCurve) )->default(JSONWebKeyTypes::RSA);
            $table->dateTime('last_use')->nullable();
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
