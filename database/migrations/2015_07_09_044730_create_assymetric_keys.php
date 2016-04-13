<?php

use Illuminate\Database\Migrations\Migration;
use jwk\JSONWebKeyPublicKeyUseValues;
use jwk\JSONWebKeyTypes;
use jwa\JSONWebSignatureAndEncryptionAlgorithms;
use oauth2\OAuth2Protocol;

/**
 * Class CreateAssymetricKeys
 */
final class CreateAssymetricKeys extends Migration
{

    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('oauth2_asymmetric_keys', function ($table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->text('pem_content');
            $table->string('kid');
            $table->boolean('active')->default(true);

            $table->enum
            (
                'usage',
                JSONWebKeyPublicKeyUseValues::$valid_uses
            )->default(JSONWebKeyPublicKeyUseValues::Signature);

            $table->enum('class_name', array('ClientPublicKey', 'ServerPrivateKey'))->default('ClientPublicKey');

            $table->enum(
                'type',
                array
                (
                    JSONWebKeyTypes::RSA,
                    JSONWebKeyTypes::EllipticCurve
                )
            )->default(JSONWebKeyTypes::RSA);

            $table->dateTime('last_use')->nullable();
            $table->text('password')->nullable();
            $table->dateTime('valid_from');
            $table->dateTime('valid_to');

            $table->enum
            (
                'alg',
                array_merge
                (
                    OAuth2Protocol::$supported_signing_algorithms_rsa,
                    OAuth2Protocol::$supported_key_management_algorithms
                )
            )
            ->default
            (
                JSONWebSignatureAndEncryptionAlgorithms::None
            );

            // FK
            $table->bigInteger("oauth2_client_id")->unsigned()->nullable();
            $table->index('oauth2_client_id');
            $table->foreign('oauth2_client_id')
                ->references('id')
                ->on('oauth2_client')
                ->onDelete('cascade')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('oauth2_asymmetric_keys', function ($table) {
            $table->dropForeign('oauth2_client_id');
        });
        Schema::dropIfExists('oauth2_asymmetric_keys');
    }

}
