<?php

use oauth2\models\IClient;
use auth\OpenIdUser;

/**
 * Class OAuth2ApplicationSeeder
 * This seeder is only for testing purposes
 */
class TestSeeder extends Seeder {

    public function run()
    {

        Eloquent::unguard();

        DB::table('server_configuration')->delete();
        DB::table('server_extensions')->delete();
        DB::table('oauth2_client_api_scope')->delete();
        DB::table('oauth2_api_scope')->delete();
        DB::table('oauth2_api')->delete();
        DB::table('oauth2_resource_server')->delete();
        DB::table('oauth2_client_authorized_uri')->delete();
        DB::table('oauth2_client_api_scope')->delete();
        DB::table('oauth2_access_token')->delete();
        DB::table('oauth2_refresh_token')->delete();
        DB::table('oauth2_client')->delete();
        DB::table('openid_users')->delete();

        ResourceServer::create(
            array(
                'friendly_name'   => 'test resource server',
                'host'            => 'https://www.resource.test1.com',
                'ip'              => '127.0.0.1'
            )
        );

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


        $resource_server = ResourceServer::first();

        Api::create(
            array(
                'name'            => 'test api user activities',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id
            )
        );

        $api = Api::where('name','=','test api user activities')->first();

        ApiScope::create(
            array(
                'name'               => 'https://www.test.com/users/activities.read',
                'short_description'  => 'User Activities Read Access',
                'description'        =>  'User Activities Read Access',
                'api_id'             => $api->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => 'https://www.test.com/users/activities.write',
                'short_description'  => 'User Activities Write Access',
                'description'        =>  'User Activities Write Access',
                'api_id'             => $api->id,
            )
        );

        ApiScope::create(
            array(
                'name'               => 'https://www.test.com/users/activities.read.write',
                'short_description'  => 'User Activities Read/Write Access',
                'description'        =>  'User Activities Read/Write Access',
                'api_id'             => $api->id,
            )
        );


        OpenIdUser::create(
            array(
                'identifier'=>'sebastian.marcet',
                'external_id'=>'smarcet@gmail.com',
                'last_login_date'=>gmdate("Y-m-d H:i:s", time())
            )
        );

        $user = OpenIdUser::where('external_id','=','smarcet@gmail.com')->first();

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
