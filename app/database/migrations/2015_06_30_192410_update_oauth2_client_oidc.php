<?php

use Illuminate\Database\Migrations\Migration;
use oauth2\OAuth2Protocol;
use oauth2\models\IClient;
use jwa\JSONWebSignatureAndEncryptionAlgorithms;
/**
 * Class UpdateOauth2Client
 */
class UpdateOauth2ClientOIDC extends Migration
{

    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {

        Schema::table('oauth2_client', function($table) {
            $table->dateTime('client_secret_expires_at')->nullable();
            $table->string('contacts')->nullable();
            $table->string('logo_uri')->nullable();
            $table->string('tos_uri')->nullable();
            $table->string('post_logout_redirect_uris')->nullable();
            $table->string('logout_uri')->nullable();
            $table->boolean('logout_session_required')->default(false);
            $table->boolean('logout_use_iframe')->default(false);
            $table->string('policy_uri')->nullable();
            $table->string('jwks_uri')->nullable();
            $table->integer('default_max_age')->default(-1);
            $table->boolean('require_auth_time')->default(false);
            // http://openid.net/specs/openid-connect-core-1_0.html#ClientAuthentication
            $table->enum('token_endpoint_auth_method', OAuth2Protocol::$token_endpoint_auth_methods)->default(OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretBasic);
            $table->enum("token_endpoint_auth_signing_alg", OAuth2Protocol::$supported_signing_algorithms)->default(JSONWebSignatureAndEncryptionAlgorithms::None);
            $table->enum('subject_type', Client::$valid_subject_types)->default(IClient::SubjectType_Public);

            $table->enum("userinfo_signed_response_alg", OAuth2Protocol::$supported_signing_algorithms)->default(JSONWebSignatureAndEncryptionAlgorithms::None);
            // encryption
            $table->enum("userinfo_encrypted_response_alg", OAuth2Protocol::$supported_key_management_algorithms)->default(JSONWebSignatureAndEncryptionAlgorithms::None);
            $table->enum("userinfo_encrypted_response_enc", OAuth2Protocol::$supported_content_encryption_algorithms)->default(JSONWebSignatureAndEncryptionAlgorithms::None);

            $table->enum("id_token_signed_response_alg",    OAuth2Protocol::$supported_signing_algorithms)->default(JSONWebSignatureAndEncryptionAlgorithms::None);
            // encryption
            $table->enum("id_token_encrypted_response_alg", OAuth2Protocol::$supported_key_management_algorithms)->default(JSONWebSignatureAndEncryptionAlgorithms::None);
            $table->enum("id_token_encrypted_response_enc", OAuth2Protocol::$supported_content_encryption_algorithms)->default(JSONWebSignatureAndEncryptionAlgorithms::None);

        });

        DB::statement("ALTER TABLE oauth2_client MODIFY COLUMN client_type ENUM('PUBLIC','CONFIDENTIAL') default 'CONFIDENTIAL';");
        DB::statement("ALTER TABLE oauth2_client MODIFY COLUMN application_type ENUM('WEB_APPLICATION','JS_CLIENT','SERVICE', 'NATIVE') default 'WEB_APPLICATION';");
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('oauth2_client', function($table) {
            $table->dropColumn('client_secret_expires_at');
            $table->dropColumn('contacts');
            $table->dropColumn('logo_uri');
            $table->dropColumn('tos_uri');
            $table->dropColumn('post_logout_redirect_uris');
            $table->dropColumn('logout_uri');
            $table->dropColumn('logout_session_required');
            $table->dropColumn('logout_use_iframe');
            $table->dropColumn('policy_uri');
            $table->dropColumn('jwks_uri');
            $table->dropColumn('default_max_age');
            $table->dropColumn('require_auth_time');
            $table->dropColumn('token_endpoint_auth_method');
            $table->dropColumn('token_endpoint_auth_signing_alg');
            $table->dropColumn('subject_type');
            $table->dropColumn('userinfo_signed_response_alg');
            $table->dropColumn('userinfo_encrypted_response_alg');
            $table->dropColumn('userinfo_encrypted_response_enc');
            $table->dropColumn('id_token_signed_response_alg');
            $table->dropColumn('id_token_encrypted_response_alg');
            $table->dropColumn('id_token_encrypted_response_enc');
        });

        DB::statement("ALTER TABLE oauth2_client MODIFY COLUMN application_type ENUM('WEB_APPLICATION','JS_CLIENT','SERVICE') default 'WEB_APPLICATION';");
    }

}
