<?php

use oauth2\models\IClient;
use auth\OpenIdUser;
use utils\services\IAuthService;
/**
 * Class OAuth2ApplicationSeeder
 * This seeder is only for testing purposes
 */
class TestSeeder extends Seeder {

    public function run()
    {

        Eloquent::unguard();

        DB::table('banned_ips')->delete();
        DB::table('user_exceptions_trail')->delete();
        DB::table('server_configuration')->delete();
        DB::table('server_extensions')->delete();
        DB::table('oauth2_client_api_scope')->delete();
        DB::table('oauth2_api_scope')->delete();
        DB::table('oauth2_api')->delete();
        DB::table('oauth2_client_authorized_uri')->delete();
        DB::table('oauth2_client_api_scope')->delete();
        DB::table('oauth2_access_token')->delete();
        DB::table('oauth2_refresh_token')->delete();
        DB::table('oauth2_client')->delete();
        DB::table('openid_trusted_sites')->delete();
        DB::table('openid_associations')->delete();
        DB::table('openid_users')->delete();
        DB::table('oauth2_resource_server')->delete();

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
                'description'     => 'OpenID Simple Registation is an extension to the OpenID Authentication protocol that allows for very light-weight profile exchange. It is designed to pass eight commonly requested pieces of information when an End User goes to register a new account with a web service',
                'view_name'       => 'extensions.sreg',
            )
        );

        ServerExtension::create(
            array(
                'name'            => 'OAUTH2',
                'namespace'       => 'http://specs.openid.net/extensions/oauth/2.0',
                'active'          => true,
                'extension_class' => 'openid\extensions\implementations\OpenIdOAuth2Extension',
                'description'     => 'The OpenID OAuth2 Extension describes how to make the OpenID Authentication and OAuth2 Core specifications work well togethe',
                'view_name'       => 'extensions.oauth2',
            )
        );

        ResourceServer::create(
            array(
                'friendly_name'   => 'test resource server',
                'host'            => 'dev.openstackid.com',
                'ip'              => '127.0.0.1'
            )
        );

        $resource_server = ResourceServer::first();


        //create api endpoints

        Api::create(
            array(
                'name'            => 'create resource server',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server',
                'http_method'     => 'POST'
            )
        );

        Api::create(
            array(
                'name'            => 'get resource server',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server/{id}',
                'http_method'     => 'GET'
            )
        );

        Api::create(
            array(
                'name'            => 'resource server regenerate secret',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server/regenerate-client-secret/{id}',
                'http_method'     => 'GET'
            )
        );

        Api::create(
            array(
                'name'            => 'resource server get page',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );

        Api::create(
            array(
                'name'            => 'resource server delete',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server/{id}',
                'http_method'     => 'DELETE'
            )
        );

        Api::create(
            array(
                'name'            => 'resource server update',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server',
                'http_method'     => 'PUT'
            )
        );

        Api::create(
            array(
                'name'            => 'resource server update status',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
                'route'           => '/api/v1/resource-server/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        $resource_server_api_create = Api::where('name','=','create resource server')->first();
        $resource_server_api_get = Api::where('name','=','get resource server')->first();
        $resource_server_api_get_page = Api::where('name','=','resource server get page')->first();
        $resource_server_api_regenerate = Api::where('name','=','resource server regenerate secret')->first();
        $resource_server_api_delete = Api::where('name','=','resource server delete')->first();
        $resource_server_api_update = Api::where('name','=','resource server update')->first();
        $resource_server_api_update_status = Api::where('name','=','resource server update status')->first();

        $current_realm = Config::get('app.url');


        // create api scopes

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/read',$current_realm),
                'short_description'  => 'Resource Server Read Access',
                'description'        => 'Resource Server Read Access',
                'api_id'             => $resource_server_api_get->id,
            )
        );


        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/read.page',$current_realm),
                'short_description'  => 'Resource Server Page Read Access',
                'description'        => 'Resource Server Page Read Access',
                'api_id'             => $resource_server_api_get_page->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/write',$current_realm),
                'short_description'  => 'Resource Server Write Access',
                'description'        => 'Resource Server Write Access',
                'api_id'             => $resource_server_api_create->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/delete',$current_realm),
                'short_description'  => 'Resource Server Delete Access',
                'description'        => 'Resource Server Delete Access',
                'api_id'             => $resource_server_api_delete->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/update',$current_realm),
                'short_description'  => 'Resource Server Update Access',
                'description'        => 'Resource Server Update Access',
                'api_id'             => $resource_server_api_update->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/update.status',$current_realm),
                'short_description'  => 'Resource Server Update Status',
                'description'        => 'Resource Server Update Status',
                'api_id'             => $resource_server_api_update_status->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/resource-server/regenerate.secret',$current_realm),
                'short_description'  => 'Resource Server Regenerate Client Secret',
                'description'        => 'Resource Server Regenerate Client Secret',
                'api_id'             => $resource_server_api_regenerate->id,
            )
        );

        // create users and clients ...
        OpenIdUser::create(
            array(
                'identifier'=>'sebastian.marcet',
                'external_id'=>'smarcet@gmail.com',
                'last_login_date'=>gmdate("Y-m-d H:i:s", time())
            )
        );

        $user = OpenIdUser::where('external_id','=','smarcet@gmail.com')->first();

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
                'user_id'              => $user->id,
                'rotate_refresh_token' => true,
                'use_refresh_token'    => true
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
                'user_id'              => $user->id,
                'rotate_refresh_token' => false,
                'use_refresh_token'    => false
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
                'user_id'              => $user->id,
                'rotate_refresh_token' => false,
                'use_refresh_token'    => false
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
                'resource_server_id'   => $resource_server->id,
                'rotate_refresh_token' => false,
                'use_refresh_token'    => false
            )
        );

        $client_confidential = Client::where('app_name','=','oauth2_test_app')->first();
        $client_public       = Client::where('app_name','=','oauth2_test_app_public')->first();
        //attach scopes
        $scopes = ApiScope::get();
        foreach($scopes as $scope){
            $client_confidential->scopes()->attach($scope->id);
            $client_public->scopes()->attach($scope->id);
        }
        //add uris
        ClientAuthorizedUri::create(
            array(
                'uri'=>'https://www.test.com/oauth2',
                'client_id'=>$client_confidential->id
            )
        );

        ClientAuthorizedUri::create(
            array(
                'uri'=>'https://www.test.com/oauth2',
                'client_id'=>$client_public->id
            )
        );
    }
}

