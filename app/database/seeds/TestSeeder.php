<?php

use oauth2\models\IClient;
use auth\User;
use utils\services\IAuthService;
use \jwk\JSONWebKeyPublicKeyUseValues;
use \jwk\JSONWebKeyTypes;
use \oauth2\OAuth2Protocol;

/**
 * Class OAuth2ApplicationSeeder
 * This seeder is only for testing purposes
 */
class TestSeeder extends Seeder {

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
                'name'               => 'openid',
                'short_description'  => 'OIDC',
                'description'        => 'OIDC',
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

        Client::create(
            array(
                'app_name'             => 'oauth2_test_app',
                'app_description'      => 'oauth2_test_app',
                'app_logo'             => null,
                'client_id'            => 'Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client',
                'client_secret'        => 'ITc/6Y5N7kOtGKhg',
                'client_type'          => IClient::ClientType_Confidential,
                'application_type'     => IClient::ApplicationType_Web_App,
                'token_endpoint_auth_method' => OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretBasic,
                'user_id'              => $user->id,
                'rotate_refresh_token' => true,
                'use_refresh_token'    => true,
                'redirect_uris' => 'https://www.test.com/oauth2'
            )
        );

        Client::create(
            array(
                'app_name'             => 'oauth2.service',
                'app_description'      => 'oauth2.service',
                'app_logo'             => null,
                'client_id'            => '11z87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client',
                'client_secret'        => '11c/6Y5N7kOtGKhg',
                'client_type'          => IClient::ClientType_Confidential,
                'application_type'     => IClient::ApplicationType_Service,
                'user_id'              => $user->id,
                'rotate_refresh_token' => true,
                'use_refresh_token'    => true,
                'redirect_uris' => 'https://www.test.com/oauth2'
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
                'user_id'              => $user->id,
                'rotate_refresh_token' => false,
                'use_refresh_token'    => false,
                'redirect_uris' => 'https://www.test.com/oauth2'
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
                'client_secret'        => '123456789',
                'client_type'          =>  IClient::ClientType_Confidential,
                'application_type'     => IClient::ApplicationType_Service,
                'resource_server_id'   => $resource_server->id,
                'rotate_refresh_token' => false,
                'use_refresh_token'    => false
            )
        );

        $client_confidential = Client::where('app_name','=','oauth2_test_app')->first();
        $client_public       = Client::where('app_name','=','oauth2_test_app_public')->first();
        $client_service      = Client::where('app_name','=','oauth2.service')->first();

        //attach all scopes
        $scopes = ApiScope::get();
        foreach($scopes as $scope){
            $client_confidential->scopes()->attach($scope->id);
            $client_public->scopes()->attach($scope->id);
            $client_service->scopes()->attach($scope->id);
        }

        $now =  new \DateTime('now');

        $public_key_1 = ClientPublicKey::buildFromPEM(
            'public_key_1',
            JSONWebKeyTypes::RSA,
            JSONWebKeyPublicKeyUseValues::Encryption,
            TestKeys::$public_key_pem,
            true,
            $now,
            $now->add(new \DateInterval('P31D'))
        );

        $public_key_1->oauth2_client_id = $client_confidential->id;
        $public_key_1->save();

        $public_key_2 = ClientPublicKey::buildFromPEM(
            'public_key_2',
            JSONWebKeyTypes::RSA,
            JSONWebKeyPublicKeyUseValues::Signature,
            TestKeys::$public_key2_pem,
            true,
            $now,
            $now->add(new \DateInterval('P31D'))
        );

        $public_key_2->oauth2_client_id = $client_confidential->id;
        $public_key_2->save();

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
                'logo'               => asset('img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'api',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Api CRUD operations',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
            )
        );


        Api::create(
            array(
                'name'            => 'api-endpoint',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Api Endpoints CRUD operations',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'api-scope',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Api Scopes CRUD operations',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'users',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'User Info',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'public-clouds',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Marketplace Public Clouds',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'private-clouds',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Marketplace Private Clouds',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
            )
        );

        Api::create(
            array(
                'name'            => 'consultants',
                'logo'            =>  null,
                'active'          =>  true,
                'Description'     => 'Marketplace Consultants',
                'resource_server_id' => $resource_server->id,
                'logo'               => asset('img/apis/server.png')
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