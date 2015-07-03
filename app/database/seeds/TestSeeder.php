<?php

use oauth2\models\IClient;
use auth\User;
use utils\services\IAuthService;
use \jwk\JSONWebKeyPublicKeyUseValues;
use \jwk\JSONWebKeyTypes;
use \oauth2\OAuth2Protocol;
use \jwa\JSONWebSignatureAndEncryptionAlgorithms;
/**
 * Class OAuth2ApplicationSeeder
 * This seeder is only for testing purposes
 */
class TestSeeder extends Seeder {

static $client_private_key_1 = <<<PPK
-----BEGIN RSA PRIVATE KEY-----
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
-----END RSA PRIVATE KEY-----
PPK;

    static $client_public_key_1 = <<<PPK
-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAkjiUI6n3Fq140AipaLxN
IPCzEItQFcY8G5Xd17u7InM3H542+34PdBpwR66miQUgJK+rtfaot/v4QPj4/0Bn
Yc78BhI0Mp3tVEH95jjIrhDMZoRFfSQsAhiom5NTP1B5XiiyRjzkO1+7a29JST5t
IQUIS2U345DMWyf3GNlC1cBAfgI+PrRo3gLby/iW5EF/Mqq0ZUIOuggZ7r8kU2aU
hXILFx2w9V/y90DwruJdzZ0TesbsFit2nM3Axie7HX2wIpbl2hyvvhX/AxZ0NPud
Vh58wNogsKOMUN6guU+RzL5L6vF+QjfzBCtOE+CRmUD60E0LdQHzElBcF0tbc2cj
2YelZ0Dp+4NEBDjCNsSv//5hHacUxxXQdwwotLUV85iErEZgcGyMNnTMsw7JIh39
UBgOEmQgfpfOUlH+/5WmRO+kskvPCACz1SR8gzAKz9Nu9r3UyE+gWaZzM2+CpQ1s
zEd94MIapHxJw9vHogL7sNkjmZ34Y9eQmoCVevqDVpYEdTtLsg9H49+pEndQHI6l
GAB7QlsPLN8A17L2l3p68BFcYkSZR4GuXAyQguq3KzWYDZ9PjWAV5lhVg6K3GaV7
fvn2pKCk4P5Y5hZt08fholt3k/5Gc82CP6rfgQFi7HnpBJKRauoIdsvUPvXZYTLl
TaE5jLBAwxm+wF6Ue/nRPJMCAwEAAQ==
-----END PUBLIC KEY-----
PPK;


    static $client_private_key_2 = <<<PPK
-----BEGIN RSA PRIVATE KEY-----
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
-----END RSA PRIVATE KEY-----
PPK;

    static $client_public_key_2 = <<<PPK
-----BEGIN PUBLIC KEY-----
MIICITANBgkqhkiG9w0BAQEFAAOCAg4AMIICCQKCAgBHhlSRoBMo9uYJyHPr0g54
EzzKrpjNBUDqnTztggsIfXR3A73TolGmeXTECu+QIAyEtGGDylp4cJhyworIwzdA
fMCY9Xux5B+Vo0Kyte2JMvwzanNLhiT21rVw56ZfyxkCJKUxz3wba0kIWQyW+kvw
LhvbQzmexHnQs15qsfP4o2MEFVTCH2ohQ57OJ8BOSU8XfddCWommmFAQcQwGXYh9
woky4NOpGBqbnGBXgWF2rnbD0GYL1Sd3OSrgTHG3WrG95UizOcV9uijI8vWxczVl
P7sriaY7Xetcbh+Z5AbAf9TMOuccRFaM/KlovR7sOcQDO1NzqlL/PRzCfzcNId22
Q6uV3QE2hzRKfd+lKI1YmFrVJ0SnWlSdeX7kkWn/+eQ4WfkK9hmv4/0bzQYo1XCo
pEpjefZoWiErCAt4nEt2wr6f6BmSjTGVajGlhn7q8wwBBHfjAUfCgIAk8uGNGWke
gvytTYywLnyNEF8tHcdg+We+W6itqJ5bQNFHa9uxX+haURoTOcGqxN8n2LPcLoLU
7xqZY23wpeXO7anzqheGSQUHb56rWqcCRQ9jF/1RLCyyVd9eOEZF6Ke53qpLQxib
hqnZfba0zOKqhbxod6RSOswSF3wwHoIj+6SRNKgui4DDcMMc+bsndr/KUDxaYpiu
In2KVE6kd5a0t8BHgdu/yQIDAQAB
-----END PUBLIC KEY-----
PPK;


    public function run()
    {

        Eloquent::unguard();

        $member_table = <<<SQL
      CREATE TABLE Member
      (
          ID integer primary key,
          FirstName varchar(50),
          Surname varchar(50),
          Email varchar(254),
          Password varchar(254),
          PasswordEncryption varchar(50),
          Salt varchar(50)
      );
SQL;

        DB::connection('os_members')->statement($member_table);

        Member::create(
            array(
                'ID'   => 1,
                'FirstName' => 'Sebastian',
                'Surname' => 'Marcet',
                'Email' => 'sebastian@tipit.net',
                'Password' => '1qaz2wsx',
                'PasswordEncryption' => 'none',
                'Salt' => 'none',
            )
        );

        DB::table('banned_ips')->delete();
        DB::table('user_exceptions_trail')->delete();
        DB::table('server_configuration')->delete();
        DB::table('server_extensions')->delete();

        DB::table('oauth2_client_api_scope')->delete();
        DB::table('oauth2_client_authorized_uri')->delete();
        DB::table('oauth2_access_token')->delete();
        DB::table('oauth2_refresh_token')->delete();
        DB::table('oauth2_assymetric_keys')->delete();
        DB::table('oauth2_client')->delete();

        DB::table('openid_trusted_sites')->delete();
        DB::table('openid_associations')->delete();
        DB::table('user_actions')->delete();
        DB::table('openid_users')->delete();

        DB::table('oauth2_api_endpoint_api_scope')->delete();
        DB::table('oauth2_api_endpoint')->delete();
        DB::table('oauth2_api_scope')->delete();
        DB::table('oauth2_api')->delete();
        DB::table('oauth2_resource_server')->delete();

        $this->seedServerConfiguration();
        $this->seedServerExtensions();

        $current_realm          = Config::get('app.url');
        $components             = parse_url($current_realm);

        ResourceServer::create(
            array(
                'friendly_name'   => 'test resource server',
                'host'            => $components['host'],
                'ip'              => '127.0.0.1'
            )
        );

        $resource_server = ResourceServer::first();

        $this->seedApis();
        //scopes

        ApiScope::create(
            array(
                'name'               => OAuth2Protocol::OpenIdConnect_Scope,
                'short_description'  => 'OIDC',
                'description'        => 'OIDC',
                'api_id'             => null,
                'system'             => true,
                'default'            => true,
                'active'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => OAuth2Protocol::OfflineAccess_Scope,
                'short_description'  => 'allow to emit refresh tokens (offline access without user presence)',
                'description'        => 'allow to emit refresh tokens (offline access without user presence)',
                'api_id'             => null,
                'system'             => true,
                'default'            => true,
                'active'             => true,
            )
        );

        $this->seedResourceServerScopes();
        $this->seedApiScopes();
        $this->seedApiEndpointScopes();
        $this->seedApiScopeScopes();
        $this->seedUsersScopes();
        $this->seedPublicCloudScopes();
        $this->seedPrivateCloudScopes();
        $this->seedConsultantScopes();
        //endpoints
        $this->seedResourceServerEndpoints();
        $this->seedApiEndpoints();
        $this->seedApiEndpointEndpoints();
        $this->seedScopeEndpoints();
        $this->seedUsersEndpoints();
        $this->seedPublicCloudsEndpoints();
        $this->seedPrivateCloudsEndpoints();
        $this->seedConsultantsEndpoints();
        //clients
        $this->seedTestUsersAndClients();
    }

    private function seedServerConfiguration(){
        ServerConfiguration::create(
            array(
                'key'   => 'Private.Association.Lifetime',
                'value' => '240',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'Session.Association.Lifetime',
                'value' => '21600',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'MaxFailed.Login.Attempts',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'MaxFailed.LoginAttempts.2ShowCaptcha',
                'value' => '3',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'Nonce.Lifetime',
                'value' => '360',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'Assets.Url',
                'value' => 'http://www.openstack.org/',
            )
        );

        //blacklist policy config values

        ServerConfiguration::create(
            array(
                'key'   => 'BannedIpLifeTimeSeconds',
                'value' => '21600',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MinutesWithoutExceptions',
                'value' => '5',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.ReplayAttackExceptionInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MaxInvalidNonceAttempts',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.InvalidNonceInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MaxInvalidOpenIdMessageExceptionAttempts',
                'value' => '10',
            )
        );


        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.InvalidOpenIdMessageExceptionInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MaxOpenIdInvalidRealmExceptionAttempts',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.OpenIdInvalidRealmExceptionInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MaxInvalidOpenIdMessageModeAttempts',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.InvalidOpenIdMessageModeInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MaxInvalidOpenIdAuthenticationRequestModeAttempts',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.InvalidOpenIdAuthenticationRequestModeInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MaxAuthenticationExceptionAttempts',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.AuthenticationExceptionInitialDelay',
                'value' => '20',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'AuthorizationCodeRedeemPolicy.MinutesWithoutExceptions',
                'value' => '5',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'AuthorizationCodeRedeemPolicy.MaxAuthCodeReplayAttackAttempts',
                'value' => '3',
            )
        );


        ServerConfiguration::create(
            array(
                'key'   => 'AuthorizationCodeRedeemPolicy.AuthCodeReplayAttackInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'AuthorizationCodeRedeemPolicy.MaxInvalidAuthorizationCodeAttempts',
                'value' => '3',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'AuthorizationCodeRedeemPolicy.InvalidAuthorizationCodeInitialDelay',
                'value' => '10',
            )
        );



    }

    private function seedServerExtensions(){
        ServerExtension::create(
            array(
                'name'            => 'AX',
                'namespace'       => 'http://openid.net/srv/ax/1.0',
                'active'          => false,
                'extension_class' => 'openid\extensions\implementations\OpenIdAXExtension',
                'description'     => 'OpenID service extension for exchanging identity information between endpoints',
                'view_name'       =>'extensions.ax',
            )
        );

        ServerExtension::create(
            array(
                'name'            => 'SREG',
                'namespace'       => 'http://openid.net/extensions/sreg/1.1',
                'active'          => true,
                'extension_class' => 'openid\extensions\implementations\OpenIdSREGExtension',
                'description'     => 'OpenID Simple Registration is an extension to the OpenID Authentication protocol that allows for very light-weight profile exchange.',
                'view_name'       => 'extensions.sreg',
            )
        );

        ServerExtension::create(
            array(
                'name'            => 'OAUTH2',
                'namespace'       => 'http://specs.openid.net/extensions/oauth/2.0',
                'active'          => true,
                'extension_class' => 'openid\extensions\implementations\OpenIdOAuth2Extension',
                'description'     => 'The OpenID OAuth2 Extension describes how to make the OpenID Authentication and OAuth2 Core specifications work well together.',
                'view_name'       => 'extensions.oauth2',
            )
        );
    }

    private function seedTestUsersAndClients(){

        $resource_server = ResourceServer::first();

        // create users and clients ...
        User::create(
            array(
                'identifier'          => 'sebastian.marcet',
                'external_identifier' => 1,
                'last_login_date'     => gmdate("Y-m-d H:i:s", time())
            )
        );

        $user = User::where('identifier','=','sebastian.marcet')->first();

        OpenIdTrustedSite::create(
            array(
                'user_id'=>$user->id,
                'realm'=>'https://www.test.com/',
                'policy'=>IAuthService::AuthorizationResponse_AllowForever
            )
        );

        $now     = new \DateTime();

        Client::create(
            array(
                'app_name'             => 'oauth2_test_app',
                'app_description'      => 'oauth2_test_app',
                'app_logo'             => null,
                'client_id'            => 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client',
                'client_secret'        => 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg',
                'client_type'          => IClient::ClientType_Confidential,
                'application_type'     => IClient::ApplicationType_Web_App,
                'token_endpoint_auth_method' => OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretBasic,
                'user_id'              => $user->id,
                'rotate_refresh_token' => true,
                'use_refresh_token'    => true,
                'redirect_uris' => 'https://www.test.com/oauth2',
                'id_token_signed_response_alg'    => JSONWebSignatureAndEncryptionAlgorithms::HS512,
                'id_token_encrypted_response_alg' => JSONWebSignatureAndEncryptionAlgorithms::RSA_OAEP_256,
                'id_token_encrypted_response_enc' => JSONWebSignatureAndEncryptionAlgorithms::A256CBC_HS512,
                'client_secret_expires_at' => $now->add(new \DateInterval('P6M')),
            )
        );

        Client::create(
            array(
                'app_name'             => 'oauth2_test_app2',
                'app_description'      => 'oauth2_test_app2',
                'app_logo'             => null,
                'client_id'            => 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x2.openstack.client',
                'client_secret'        => 'ITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhgITc/6Y5N7kOtGKhg',
                'client_type'          => IClient::ClientType_Confidential,
                'application_type'     => IClient::ApplicationType_Web_App,
                'token_endpoint_auth_method' => OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretJwt,
                'token_endpoint_auth_signing_alg' => JSONWebSignatureAndEncryptionAlgorithms::HS512,
                'subject_type' => IClient::SubjectType_Pairwise,
                'user_id'              => $user->id,
                'rotate_refresh_token' => true,
                'use_refresh_token'    => true,
                'redirect_uris' => 'https://www.test.com/oauth2',
                'id_token_signed_response_alg'    => JSONWebSignatureAndEncryptionAlgorithms::HS512,
                'id_token_encrypted_response_alg' => JSONWebSignatureAndEncryptionAlgorithms::RSA_OAEP_256,
                'id_token_encrypted_response_enc' => JSONWebSignatureAndEncryptionAlgorithms::A256CBC_HS512,
                'client_secret_expires_at' => $now->add(new \DateInterval('P6M')),
            )
        );

        Client::create(
            array(
                'app_name'             => 'oauth2.service',
                'app_description'      => 'oauth2.service',
                'app_logo'             => null,
                'client_id'            => '11z87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client',
                'client_secret'        => '11c/6Y5N7kOtGKhg11c/6Y5N7kOtGKhg11c/6Y5N7kOtGKhg11c/6Y5N7kOtGKhg',
                'client_type'          => IClient::ClientType_Confidential,
                'application_type'     => IClient::ApplicationType_Service,
                'token_endpoint_auth_method' => OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretBasic,
                'user_id'              => $user->id,
                'rotate_refresh_token' => true,
                'use_refresh_token'    => true,
                'redirect_uris' => 'https://www.test.com/oauth2',
                'client_secret_expires_at' => $now->add(new \DateInterval('P6M')),
            )
        );

        Client::create(
            array(
                'app_name'             => 'oauth2_test_app_public',
                'app_description'      => 'oauth2_test_app_public',
                'app_logo'             => null,
                'client_id'            => 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ3x.openstack.client',
                'client_secret'        => null,
                'client_type'          => IClient::ClientType_Public,
                'application_type'     => IClient::ApplicationType_Native,
                'token_endpoint_auth_method' => OAuth2Protocol::TokenEndpoint_AuthMethod_PrivateKeyJwt,
                'token_endpoint_auth_signing_alg' => JSONWebSignatureAndEncryptionAlgorithms::RS512,
                'user_id'              => $user->id,
                'rotate_refresh_token' => false,
                'use_refresh_token'    => false,
                'redirect_uris' => 'https://www.test.com/oauth2',

            )
        );

        Client::create(
            array(
                'app_name'             => 'oauth2_test_app_public_2',
                'app_description'      => 'oauth2_test_app_public_2',
                'app_logo'             => null,
                'client_id'            => 'Jiz87D8/Vcvr6fvQbH4HyNgwKlfSyQ2x.openstack.client',
                'client_secret'        => null,
                'client_type'          => IClient::ClientType_Public,
                'application_type'     => IClient::ApplicationType_JS_Client,
                'user_id'              => $user->id,
                'rotate_refresh_token' => false,
                'use_refresh_token'    => false,
                'redirect_uris' => 'https://www.test.com/oauth2'
            )
        );

        Client::create(
            array(
                'app_name'             => 'resource_server_client',
                'app_description'      => 'resource_server_client',
                'app_logo'             => null,
                'client_id'            => 'resource.server.1.openstack.client',
                'client_secret'        => '123456789123456789123456789123456789123456789',
                'client_type'          =>  IClient::ClientType_Confidential,
                'application_type'     => IClient::ApplicationType_Service,
                'token_endpoint_auth_method' => OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretBasic,
                'resource_server_id'   => $resource_server->id,
                'rotate_refresh_token' => false,
                'use_refresh_token'    => false,
                'client_secret_expires_at' => $now->add(new \DateInterval('P6M')),
            )
        );

        $client_confidential  = Client::where('app_name','=','oauth2_test_app')->first();
        $client_confidential2 = Client::where('app_name','=','oauth2_test_app2')->first();
        $client_public        = Client::where('app_name','=','oauth2_test_app_public')->first();
        $client_service       = Client::where('app_name','=','oauth2.service')->first();

        //attach all scopes
        $scopes = ApiScope::get();
        foreach($scopes as $scope)
        {
            $client_confidential->scopes()->attach($scope->id);
            $client_confidential2->scopes()->attach($scope->id);
            $client_public->scopes()->attach($scope->id);
            $client_service->scopes()->attach($scope->id);
        }

        $now =  new \DateTime('now');
        $to = new \DateTime('now');
        $to->add(new \DateInterval('P31D'));

        $public_key_1 = ClientPublicKey::buildFromPEM(
            'public_key_1',
            JSONWebKeyTypes::RSA,
            JSONWebKeyPublicKeyUseValues::Encryption,
            self::$client_public_key_1,
            JSONWebSignatureAndEncryptionAlgorithms::RSA_OAEP_256,
            true,
            $now,
            $to
        );

        $public_key_1->oauth2_client_id = $client_confidential->id;
        $public_key_1->save();

        $public_key_2 = ClientPublicKey::buildFromPEM(
            'public_key_2',
            JSONWebKeyTypes::RSA,
            JSONWebKeyPublicKeyUseValues::Signature,
            self::$client_public_key_2,
            JSONWebSignatureAndEncryptionAlgorithms::RS512,
            true,
            $now,
            $to
        );

        $public_key_2->oauth2_client_id = $client_confidential->id;
        $public_key_2->save();

        // confidential client 2
        $public_key_11 = ClientPublicKey::buildFromPEM(
            'public_key_1',
            JSONWebKeyTypes::RSA,
            JSONWebKeyPublicKeyUseValues::Encryption,
            self::$client_public_key_1,
            JSONWebSignatureAndEncryptionAlgorithms::RSA_OAEP_256,
            true,
            $now,
            $to
        );

        $public_key_11->oauth2_client_id = $client_confidential2->id;
        $public_key_11->save();

        $public_key_22 = ClientPublicKey::buildFromPEM(
            'public_key_2',
            JSONWebKeyTypes::RSA,
            JSONWebKeyPublicKeyUseValues::Signature,
            self::$client_public_key_2,
            JSONWebSignatureAndEncryptionAlgorithms::RS512,
            true,
            $now,
            $to
        );

        $public_key_22->oauth2_client_id = $client_confidential2->id;
        $public_key_22->save();

        // public native client
        $public_key_33 = ClientPublicKey::buildFromPEM(
            'public_key_33',
            JSONWebKeyTypes::RSA,
            JSONWebKeyPublicKeyUseValues::Encryption,
            self::$client_public_key_1,
            JSONWebSignatureAndEncryptionAlgorithms::RSA_OAEP_256,
            true,
            $now,
            $to
        );

        $public_key_33->oauth2_client_id = $client_public->id;
        $public_key_33->save();

        $public_key_44 = ClientPublicKey::buildFromPEM(
            'public_key_44',
            JSONWebKeyTypes::RSA,
            JSONWebKeyPublicKeyUseValues::Signature,
            self::$client_public_key_2,
            JSONWebSignatureAndEncryptionAlgorithms::RS512,
            true,
            $now,
            $to
        );

        $public_key_44->oauth2_client_id = $client_public->id;
        $public_key_44->save();

        // server private keys

        $pkey_1 = ServerPrivateKey::build
        (
            'server_key_enc',
            $now,
            $to,
            JSONWebKeyTypes::RSA,
            JSONWebKeyPublicKeyUseValues::Encryption,
            JSONWebSignatureAndEncryptionAlgorithms::RSA1_5,
            true,
            TestKeys::$private_key_pem
        );

        $pkey_1->save();


        $pkey_2 = ServerPrivateKey::build
        (
            'server_key_sig',
            $now,
            $to,
            JSONWebKeyTypes::RSA,
            JSONWebKeyPublicKeyUseValues::Signature,
            JSONWebSignatureAndEncryptionAlgorithms::RS512,
            true,
            TestKeys::$private_key_pem
        );

        $pkey_2->save();
    }

    private function seedApis(){
        $resource_server = ResourceServer::first();

        Api::create(
            array(
                'name'               => 'resource-server',
                'logo'               =>  null,
                'active'             =>  true,
                'Description'        => 'Resource Server CRUD operations',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('/assets/img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'api',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Api CRUD operations',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('/assets/img/apis/server.png')
            )
        );


        Api::create(
            array(
                'name'            => 'api-endpoint',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Api Endpoints CRUD operations',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('/assets/img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'api-scope',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Api Scopes CRUD operations',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('/assets/img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'users',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'User Info',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('/assets/img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'public-clouds',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Marketplace Public Clouds',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('/assets/img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'private-clouds',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Marketplace Private Clouds',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('/assets/img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'consultants',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Marketplace Consultants',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('/assets/img/apis/server.png')
            )
        );

    }

    private function seedResourceServerScopes(){

        $resource_server        = Api::where('name','=','resource-server')->first();
        $current_realm          = Config::get('app.url');

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/read',$current_realm),
                'short_description'  => 'Resource Server Read Access',
                'description'        => 'Resource Server Read Access',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/read.page',$current_realm),
                'short_description'  => 'Resource Server Page Read Access',
                'description'        => 'Resource Server Page Read Access',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/write',$current_realm),
                'short_description'  => 'Resource Server Write Access',
                'description'        => 'Resource Server Write Access',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/delete',$current_realm),
                'short_description'  => 'Resource Server Delete Access',
                'description'        => 'Resource Server Delete Access',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/update',$current_realm),
                'short_description'  => 'Resource Server Update Access',
                'description'        => 'Resource Server Update Access',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/update.status',$current_realm),
                'short_description'  => 'Resource Server Update Status',
                'description'        => 'Resource Server Update Status',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/regenerate.secret',$current_realm),
                'short_description'  => 'Resource Server Regenerate Client Secret',
                'description'        => 'Resource Server Regenerate Client Secret',
                'api_id'             => $resource_server->id,
                'system'             => true,
            )
        );

    }

    private function seedApiScopes(){
        $api           = Api::where('name','=','api')->first();
        $current_realm = Config::get('app.url');

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/read',$current_realm),
                'short_description'  => 'Get Api',
                'description'        => 'Get Api',
                'api_id'             => $api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/delete',$current_realm),
                'short_description'  => 'Deletes Api',
                'description'        => 'Deletes Api',
                'api_id'             => $api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/write',$current_realm),
                'short_description'  => 'Create Api',
                'description'        => 'Create Api',
                'api_id'             => $api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/update',$current_realm),
                'short_description'  => 'Update Api',
                'description'        => 'Update Api',
                'api_id'             => $api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/update.status',$current_realm),
                'short_description'  => 'Update Api Status',
                'description'        => 'Update Api Status',
                'api_id'             => $api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/read.page',$current_realm),
                'short_description'  => 'Get Api By Page',
                'description'        => 'Get Api By Page',
                'api_id'             => $api->id,
                'system'             => true,
            )
        );


    }

    private function seedApiEndpointScopes(){
        $api_endpoint  = Api::where('name','=','api-endpoint')->first();
        $current_realm = Config::get('app.url');

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/read',$current_realm),
                'short_description'  => 'Get Api Endpoint',
                'description'        => 'Get Api Endpoint',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/delete',$current_realm),
                'short_description'  => 'Deletes Api Endpoint',
                'description'        => 'Deletes Api Endpoint',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/write',$current_realm),
                'short_description'  => 'Create Api Endpoint',
                'description'        => 'Create Api Endpoint',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/update',$current_realm),
                'short_description'  => 'Update Api Endpoint',
                'description'        => 'Update Api Endpoint',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/update.status',$current_realm),
                'short_description'  => 'Update Api Endpoint Status',
                'description'        => 'Update Api Endpoint Status',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/read.page',$current_realm),
                'short_description'  => 'Get Api Endpoints By Page',
                'description'        => 'Get Api Endpoints By Page',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );


        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/add.scope',$current_realm),
                'short_description'  => 'Add required scope to endpoint',
                'description'        => 'Add required scope to endpoint',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );


        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/remove.scope',$current_realm),
                'short_description'  => 'Remove required scope to endpoint',
                'description'        => 'Remove required scope to endpoint',
                'api_id'             => $api_endpoint->id,
                'system'             => true,
            )
        );

    }

    private function seedApiScopeScopes(){

        $current_realm = Config::get('app.url');
        $api_scope              = Api::where('name','=','api-scope')->first();

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-scope/read',$current_realm),
                'short_description'  => 'Get Api Scope',
                'description'        => 'Get Api Scope',
                'api_id'             => $api_scope->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-scope/delete',$current_realm),
                'short_description'  => 'Deletes Api Scope',
                'description'        => 'Deletes Api Scope',
                'api_id'             => $api_scope->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-scope/write',$current_realm),
                'short_description'  => 'Create Api Scope',
                'description'        => 'Create Api Scope',
                'api_id'             => $api_scope->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-scope/update',$current_realm),
                'short_description'  => 'Update Api Scope',
                'description'        => 'Update Api Scope',
                'api_id'             => $api_scope->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-scope/update.status',$current_realm),
                'short_description'  => 'Update Api Scope Status',
                'description'        => 'Update Api Scope Status',
                'api_id'             => $api_scope->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-scope/read.page',$current_realm),
                'short_description'  => 'Get Api Scopes By Page',
                'description'        => 'Get Api Scopes By Page',
                'api_id'             => $api_scope->id,
                'system'             => true,
            )
        );

    }

    private function seedUsersScopes(){
        $current_realm = Config::get('app.url');
        $users    = Api::where('name','=','users')->first();

        ApiScope::create(
            array(
                'name'               => 'profile',
                'short_description'  => 'This scope value requests access to the End-Users default profile Claims',
                'description'        => 'This scope value requests access to the End-Users default profile Claims, which are: name, family_name, given_name, middle_name, nickname, preferred_username, profile, picture, website, gender, birthdate, zoneinfo, locale, and updated_at',
                'api_id'             => $users->id,
                'system'             => false,
            )
        );

        ApiScope::create(
            array(
                'name'               => 'email',
                'short_description'  => 'This scope value requests access to the email and email_verified Claims',
                'description'        => 'This scope value requests access to the email and email_verified Claims',
                'api_id'             => $users->id,
                'system'             => false,
            )
        );

        ApiScope::create(
            array(
                'name'               => 'address',
                'short_description'  => 'This scope value requests access to the address Claim.',
                'description'        => 'This scope value requests access to the address Claim.',
                'api_id'             => $users->id,
                'system'             => false,
            )
        );
    }

    private function seedPublicCloudScopes(){

        $current_realm = Config::get('app.url');
        $public_clouds    = Api::where('name','=','public-clouds')->first();

        ApiScope::create(
            array(
                'name'               => sprintf('%s/public-clouds/read',$current_realm),
                'short_description'  => 'Get Public Clouds',
                'description'        => 'Get Public Clouds',
                'api_id'             => $public_clouds->id,
                'system'             => false,
            )
        );
    }

    private function seedPrivateCloudScopes(){

        $current_realm  = Config::get('app.url');
        $private_clouds = Api::where('name','=','private-clouds')->first();

        ApiScope::create(
            array(
                'name'               => sprintf('%s/private-clouds/read',$current_realm),
                'short_description'  => 'Get Private Clouds',
                'description'        => 'Get Private Clouds',
                'api_id'             => $private_clouds->id,
                'system'             => false,
            )
        );
    }


    private function seedConsultantScopes(){

        $current_realm  = Config::get('app.url');
        $consultants = Api::where('name','=','consultants')->first();

        ApiScope::create(
            array(
                'name'               => sprintf('%s/consultants/read',$current_realm),
                'short_description'  => 'Get Consultants',
                'description'        => 'Get Consultants',
                'api_id'             => $consultants->id,
                'system'             => false,
            )
        );
    }

    private function seedResourceServerEndpoints(){

        $current_realm  = Config::get('app.url');
        $resource_server = Api::where('name','=','resource-server')->first();

        ApiEndpoint::create(
            array(
                'name'            => 'create-resource-server',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => '/api/v1/resource-servers',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'get-resource-server',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => '/api/v1/resource-servers/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-regenerate-secret',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => '/api/v1/resource-servers/{id}/client-secret',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-get-page',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => '/api/v1/resource-servers',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-delete',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => '/api/v1/resource-servers/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-update',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => '/api/v1/resource-servers',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-update-status',
                'active'          =>  true,
                'api_id'          => $resource_server->id,
                'route'           => '/api/v1/resource-servers/{id}/status/{active}',
                'http_method'     => 'PUT'
            )
        );

        //attach scopes to endpoints

        //resource server api scopes

        $resource_server_read_scope               = ApiScope::where('name','=',sprintf('%s/resource-server/read',$current_realm))->first();
        $resource_server_write_scope              = ApiScope::where('name','=',sprintf('%s/resource-server/write',$current_realm))->first();
        $resource_server_read_page_scope          = ApiScope::where('name','=',sprintf('%s/resource-server/read.page',$current_realm))->first();
        $resource_server_regenerate_secret_scope  = ApiScope::where('name','=',sprintf('%s/resource-server/regenerate.secret',$current_realm))->first();
        $resource_server_delete_scope             = ApiScope::where('name','=',sprintf('%s/resource-server/delete',$current_realm))->first();
        $resource_server_update_scope             = ApiScope::where('name','=',sprintf('%s/resource-server/update',$current_realm))->first();
        $resource_server_update_status_scope      = ApiScope::where('name','=',sprintf('%s/resource-server/update.status',$current_realm))->first();


        // create needs write access
        $resource_server_api_create = ApiEndpoint::where('name','=','create-resource-server')->first();
        $resource_server_api_create->scopes()->attach($resource_server_write_scope->id);

        //get needs read access
        $resource_server_api_get = ApiEndpoint::where('name','=','get-resource-server')->first();
        $resource_server_api_get->scopes()->attach($resource_server_read_scope->id);

        // get page needs read access or read page access
        $resource_server_api_get_page = ApiEndpoint::where('name','=','resource-server-get-page')->first();
        $resource_server_api_get_page->scopes()->attach($resource_server_read_scope->id);
        $resource_server_api_get_page->scopes()->attach($resource_server_read_page_scope->id);

        //regenerate secret needs write access or specific access
        $resource_server_api_regenerate = ApiEndpoint::where('name','=','resource-server-regenerate-secret')->first();
        $resource_server_api_regenerate->scopes()->attach($resource_server_write_scope->id);
        $resource_server_api_regenerate->scopes()->attach($resource_server_regenerate_secret_scope->id);

        //deletes needs delete access
        $resource_server_api_delete = ApiEndpoint::where('name','=','resource-server-delete')->first();
        $resource_server_api_delete->scopes()->attach($resource_server_delete_scope->id);

        //update needs update access
        $resource_server_api_update = ApiEndpoint::where('name','=','resource-server-update')->first();
        $resource_server_api_update->scopes()->attach($resource_server_update_scope->id);

        //update status needs update access or specific access
        $resource_server_api_update_status = ApiEndpoint::where('name','=','resource-server-update-status')->first();
        $resource_server_api_update_status->scopes()->attach($resource_server_update_scope->id);
        $resource_server_api_update_status->scopes()->attach($resource_server_update_status_scope->id);


    }

    private function seedApiEndpoints(){

        $current_realm  = Config::get('app.url');
        $api_api = Api::where('name','=','api')->first();

        ApiEndpoint::create(
            array(
                'name'            => 'get-api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api/{id}',
                'http_method'     => 'GET'
            )
        );


        ApiEndpoint::create(
            array(
                'name'            => 'delete-api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'create-api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-api-status',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'api-get-page',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => '/api/v1/api/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );

        //endpoint api scopes

        $api_read_scope               = ApiScope::where('name','=',sprintf('%s/api/read',$current_realm))->first();
        $api_write_scope              = ApiScope::where('name','=',sprintf('%s/api/write',$current_realm))->first();
        $api_read_page_scope          = ApiScope::where('name','=',sprintf('%s/api/read.page',$current_realm))->first();
        $api_delete_scope             = ApiScope::where('name','=',sprintf('%s/api/delete',$current_realm))->first();
        $api_update_scope             = ApiScope::where('name','=',sprintf('%s/api/update',$current_realm))->first();
        $api_update_status_scope      = ApiScope::where('name','=',sprintf('%s/api/update.status',$current_realm))->first();

        $endpoint_api_get                  = ApiEndpoint::where('name','=','get-api')->first();
        $endpoint_api_get->scopes()->attach($api_read_scope->id);

        $endpoint_api_get_page             = ApiEndpoint::where('name','=','api-get-page')->first();
        $endpoint_api_get_page->scopes()->attach($api_read_scope->id);
        $endpoint_api_get_page->scopes()->attach($api_read_page_scope->id);

        $endpoint_api_delete               = ApiEndpoint::where('name','=','delete-api')->first();
        $endpoint_api_delete->scopes()->attach($api_delete_scope->id);

        $endpoint_api_create               = ApiEndpoint::where('name','=','create-api')->first();
        $endpoint_api_create->scopes()->attach($api_write_scope->id);

        $endpoint_api_update               = ApiEndpoint::where('name','=','update-api')->first();
        $endpoint_api_update->scopes()->attach($api_update_scope->id);

        $endpoint_api_update_status        = ApiEndpoint::where('name','=','update-api-status')->first();
        $endpoint_api_update_status->scopes()->attach($api_update_scope->id);
        $endpoint_api_update_status->scopes()->attach($api_update_status_scope->id);
    }

    private function seedApiEndpointEndpoints(){

        $current_realm  = Config::get('app.url');
        $api_api_endpoint           = Api::where('name','=','api-endpoint')->first();

        ApiEndpoint::create(
            array(
                'name'            => 'get-api-endpoint',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => '/api/v1/api-endpoint/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'delete-api-endpoint',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => '/api/v1/api-endpoint/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'create-api-endpoint',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => '/api/v1/api-endpoint',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-api-endpoint',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => '/api/v1/api-endpoint',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-api-endpoint-status',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => '/api/v1/api-endpoint/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'api-endpoint-get-page',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => '/api/v1/api-endpoint/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );


        ApiEndpoint::create(
            array(
                'name'            => 'add-api-endpoint-scope',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => '/api/v1/api-endpoint/scope/add/{id}/{scope_id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'remove-api-endpoint-scope',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => '/api/v1/api-endpoint/scope/remove/{id}/{scope_id}',
                'http_method'     => 'GET'
            )
        );

        //endpoint api endpoint scopes

        $api_endpoint_read_scope               = ApiScope::where('name','=',sprintf('%s/api-endpoint/read',$current_realm))->first();
        $api_endpoint_write_scope              = ApiScope::where('name','=',sprintf('%s/api-endpoint/write',$current_realm))->first();
        $api_endpoint_read_page_scope          = ApiScope::where('name','=',sprintf('%s/api-endpoint/read.page',$current_realm))->first();
        $api_endpoint_delete_scope             = ApiScope::where('name','=',sprintf('%s/api-endpoint/delete',$current_realm))->first();
        $api_endpoint_update_scope             = ApiScope::where('name','=',sprintf('%s/api-endpoint/update',$current_realm))->first();
        $api_endpoint_update_status_scope      = ApiScope::where('name','=',sprintf('%s/api-endpoint/update.status',$current_realm))->first();
        $api_endpoint_add_scope_scope          = ApiScope::where('name','=',sprintf('%s/api-endpoint/add.scope',$current_realm))->first();
        $api_endpoint_remove_scope_scope       = ApiScope::where('name','=',sprintf('%s/api-endpoint/remove.scope',$current_realm))->first();

        $endpoint_api_endpoint_get                  = ApiEndpoint::where('name','=','get-api-endpoint')->first();
        $endpoint_api_endpoint_get->scopes()->attach($api_endpoint_read_scope->id);

        $endpoint_api_endpoint_get_page             = ApiEndpoint::where('name','=','api-endpoint-get-page')->first();
        $endpoint_api_endpoint_get_page->scopes()->attach($api_endpoint_read_scope->id);
        $endpoint_api_endpoint_get_page->scopes()->attach($api_endpoint_read_page_scope->id);

        $endpoint_api_endpoint_delete               = ApiEndpoint::where('name','=','delete-api-endpoint')->first();
        $endpoint_api_endpoint_delete->scopes()->attach($api_endpoint_delete_scope->id);

        $endpoint_api_endpoint_create               = ApiEndpoint::where('name','=','create-api-endpoint')->first();
        $endpoint_api_endpoint_create->scopes()->attach($api_endpoint_write_scope->id);

        $endpoint_api_endpoint_update       = ApiEndpoint::where('name','=','update-api-endpoint')->first();
        $endpoint_api_endpoint_update->scopes()->attach($api_endpoint_update_scope->id);

        $endpoint_api_add_api_endpoint_scope        = ApiEndpoint::where('name','=','add-api-endpoint-scope')->first();
        $endpoint_api_add_api_endpoint_scope->scopes()->attach($api_endpoint_write_scope->id);
        $endpoint_api_add_api_endpoint_scope->scopes()->attach($api_endpoint_add_scope_scope->id);

        $endpoint_api_remove_api_endpoint_scope        = ApiEndpoint::where('name','=','remove-api-endpoint-scope')->first();
        $endpoint_api_remove_api_endpoint_scope->scopes()->attach($api_endpoint_write_scope->id);
        $endpoint_api_remove_api_endpoint_scope->scopes()->attach($api_endpoint_remove_scope_scope->id);


        $endpoint_api_endpoint_update_status        = ApiEndpoint::where('name','=','update-api-endpoint-status')->first();
        $endpoint_api_endpoint_update_status->scopes()->attach($api_endpoint_update_scope->id);
        $endpoint_api_endpoint_update_status->scopes()->attach($api_endpoint_update_status_scope->id);

    }

    private function seedScopeEndpoints(){
        $api_scope                  = Api::where('name','=','api-scope')->first();
        $current_realm  = Config::get('app.url');
        // endpoints scopes

        ApiEndpoint::create(
            array(
                'name'            => 'get-scope',
                'active'          =>  true,
                'api_id'          => $api_scope->id,
                'route'           => '/api/v1/api-scope/{id}',
                'http_method'     => 'GET'
            )
        );


        ApiEndpoint::create(
            array(
                'name'            => 'delete-scope',
                'active'          =>  true,
                'api_id'          => $api_scope->id,
                'route'           => '/api/v1/api-scope/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'create-scope',
                'active'          =>  true,
                'api_id'          => $api_scope->id,
                'route'           => '/api/v1/api-scope',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-scope',
                'active'          =>  true,
                'api_id'          => $api_scope->id,
                'route'           => '/api/v1/api-scope',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-scope-status',
                'active'          =>  true,
                'api_id'          => $api_scope->id,
                'route'           => '/api/v1/api-scope/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'scope-get-page',
                'active'          =>  true,
                'api_id'          => $api_scope->id,
                'route'           => '/api/v1/api-scope/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );

        $api_scope_read_scope               = ApiScope::where('name','=',sprintf('%s/api-scope/read',$current_realm))->first();
        $api_scope_write_scope              = ApiScope::where('name','=',sprintf('%s/api-scope/write',$current_realm))->first();
        $api_scope_read_page_scope          = ApiScope::where('name','=',sprintf('%s/api-scope/read.page',$current_realm))->first();
        $api_scope_delete_scope             = ApiScope::where('name','=',sprintf('%s/api-scope/delete',$current_realm))->first();
        $api_scope_update_scope             = ApiScope::where('name','=',sprintf('%s/api-scope/update',$current_realm))->first();
        $api_scope_update_status_scope      = ApiScope::where('name','=',sprintf('%s/api-scope/update.status',$current_realm))->first();


        $endpoint_api_scope_get             = ApiEndpoint::where('name','=','get-scope')->first();
        $endpoint_api_scope_get->scopes()->attach($api_scope_read_scope->id);

        $endpoint_api_scope_get_page        = ApiEndpoint::where('name','=','scope-get-page')->first();
        $endpoint_api_scope_get_page->scopes()->attach($api_scope_read_scope->id);
        $endpoint_api_scope_get_page->scopes()->attach($api_scope_read_page_scope->id);

        $endpoint_api_scope_delete          = ApiEndpoint::where('name','=','delete-scope')->first();
        $endpoint_api_scope_delete->scopes()->attach($api_scope_delete_scope->id);

        $endpoint_api_scope_create          = ApiEndpoint::where('name','=','create-scope')->first();
        $endpoint_api_scope_create->scopes()->attach($api_scope_write_scope->id);

        $endpoint_api_scope_update               = ApiEndpoint::where('name','=','update-scope')->first();
        $endpoint_api_scope_update->scopes()->attach($api_scope_update_scope->id);

        $endpoint_api_scope_update_status        = ApiEndpoint::where('name','=','update-scope-status')->first();
        $endpoint_api_scope_update_status->scopes()->attach($api_scope_update_scope->id);
        $endpoint_api_scope_update_status->scopes()->attach($api_scope_update_status_scope->id);
    }

    private function seedUsersEndpoints(){
        $users                  = Api::where('name','=','users')->first();
        $current_realm  = Config::get('app.url');
        // endpoints scopes

        ApiEndpoint::create(
            array(
                'name'            => 'get-user-info',
                'active'          =>  true,
                'api_id'          => $users->id,
                'route'           => '/api/v1/users/me',
                'http_method'     => 'GET'
            )
        );
        $profile_scope = ApiScope::where('name','=','profile')->first();
        $email_scope   = ApiScope::where('name','=','email')->first();
        $address_scope = ApiScope::where('name','=','address')->first();

        $get_user_info_endpoint = ApiEndpoint::where('name','=','get-user-info')->first();
        $get_user_info_endpoint->scopes()->attach($profile_scope->id);
        $get_user_info_endpoint->scopes()->attach($email_scope->id);
        $get_user_info_endpoint->scopes()->attach($address_scope->id);
    }

    private function seedPublicCloudsEndpoints(){
        $public_clouds  = Api::where('name','=','public-clouds')->first();
        $current_realm  = Config::get('app.url');
        // endpoints scopes

        ApiEndpoint::create(
            array(
                'name'            => 'get-public-clouds',
                'active'          =>  true,
                'api_id'          => $public_clouds->id,
                'route'           => '/api/v1/marketplace/public-clouds',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'get-public-cloud',
                'active'          =>  true,
                'api_id'          => $public_clouds->id,
                'route'           => '/api/v1/marketplace/public-clouds/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'get-public-cloud-datacenters',
                'active'          =>  true,
                'api_id'          => $public_clouds->id,
                'route'           => '/api/v1/marketplace/public-clouds/{id}/data-centers',
                'http_method'     => 'GET'
            )
        );

        $public_cloud_read_scope           = ApiScope::where('name','=',sprintf('%s/public-clouds/read',$current_realm))->first();

        $endpoint_get_public_clouds            = ApiEndpoint::where('name','=','get-public-clouds')->first();
        $endpoint_get_public_clouds->scopes()->attach($public_cloud_read_scope->id);

        $endpoint_get_public_cloud        = ApiEndpoint::where('name','=','get-public-cloud')->first();
        $endpoint_get_public_cloud->scopes()->attach($public_cloud_read_scope->id);

        $endpoint_get_public_cloud_datacenters = ApiEndpoint::where('name','=','get-public-cloud-datacenters')->first();
        $endpoint_get_public_cloud_datacenters->scopes()->attach($public_cloud_read_scope->id);
    }

    private function seedPrivateCloudsEndpoints(){
        $private_clouds  = Api::where('name','=','private-clouds')->first();
        $current_realm  = Config::get('app.url');
        // endpoints scopes

        ApiEndpoint::create(
            array(
                'name'            => 'get-private-clouds',
                'active'          =>  true,
                'api_id'          => $private_clouds->id,
                'route'           => '/api/v1/marketplace/private-clouds',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'get-private-cloud',
                'active'          =>  true,
                'api_id'          => $private_clouds->id,
                'route'           => '/api/v1/marketplace/private-clouds/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'get-private-cloud-datacenters',
                'active'          =>  true,
                'api_id'          => $private_clouds->id,
                'route'           => '/api/v1/marketplace/private-clouds/{id}/data-centers',
                'http_method'     => 'GET'
            )
        );

        $private_cloud_read_scope           = ApiScope::where('name','=',sprintf('%s/private-clouds/read',$current_realm))->first();

        $endpoint_get_private_clouds            = ApiEndpoint::where('name','=','get-private-clouds')->first();
        $endpoint_get_private_clouds->scopes()->attach($private_cloud_read_scope->id);

        $endpoint_get_private_cloud        = ApiEndpoint::where('name','=','get-private-cloud')->first();
        $endpoint_get_private_cloud->scopes()->attach($private_cloud_read_scope->id);

        $endpoint_get_private_cloud_datacenters = ApiEndpoint::where('name','=','get-private-cloud-datacenters')->first();
        $endpoint_get_private_cloud_datacenters->scopes()->attach($private_cloud_read_scope->id);

    }

    private function seedConsultantsEndpoints(){

        $consultants  = Api::where('name','=','consultants')->first();
        $current_realm  = Config::get('app.url');
        // endpoints scopes

        ApiEndpoint::create(
            array(
                'name'            => 'get-consultants',
                'active'          =>  true,
                'api_id'          => $consultants->id,
                'route'           => '/api/v1/marketplace/consultants',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'get-consultant',
                'active'          =>  true,
                'api_id'          => $consultants->id,
                'route'           => '/api/v1/marketplace/consultants/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'get-consultant-offices',
                'active'          =>  true,
                'api_id'          => $consultants->id,
                'route'           => '/api/v1/marketplace/consultants/{id}/offices',
                'http_method'     => 'GET'
            )
        );

        $consultant_read_scope = ApiScope::where('name','=',sprintf('%s/consultants/read',$current_realm))->first();

        $endpoint              = ApiEndpoint::where('name','=','get-consultants')->first();
        $endpoint->scopes()->attach($consultant_read_scope->id);

        $endpoint              = ApiEndpoint::where('name','=','get-consultant')->first();
        $endpoint->scopes()->attach($consultant_read_scope->id);

        $endpoint              = ApiEndpoint::where('name','=','get-consultant-offices')->first();
        $endpoint->scopes()->attach($consultant_read_scope->id);
    }
}