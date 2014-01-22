<?php

use oauth2\models\IClient;
use auth\User;
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
        DB::table('oauth2_client_authorized_uri')->delete();
        DB::table('oauth2_access_token')->delete();
        DB::table('oauth2_refresh_token')->delete();
        DB::table('oauth2_client')->delete();

        DB::table('openid_trusted_sites')->delete();
        DB::table('openid_associations')->delete();
        DB::table('openid_users')->delete();

        DB::table('oauth2_api_endpoint_api_scope')->delete();
        DB::table('oauth2_api_endpoint')->delete();
        DB::table('oauth2_api_scope')->delete();
        DB::table('oauth2_api')->delete();
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


      // create api

        Api::create(
            array(
                'name'            => 'resource-server',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
            )
        );

        Api::create(
            array(
                'name'            => 'api',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
            )
        );

        Api::create(
            array(
                'name'            => 'api-endpoint',
                'logo'            =>  null,
                'active'          =>  true,
                'resource_server_id' => $resource_server->id,
            )
        );

        //create scopes


        $current_realm = Config::get('app.url');


        $resource_server_api        = Api::where('name','=','resource-server')->first();
        $api_api                    = Api::where('name','=','api')->first();
        $api_api_endpoint           = Api::where('name','=','api-endpoint')->first();

        // create api scopes

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/read',$current_realm),
                'short_description'  => 'Resource Server Read Access',
                'description'        => 'Resource Server Read Access',
                'api_id'             => $resource_server_api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/read.page',$current_realm),
                'short_description'  => 'Resource Server Page Read Access',
                'description'        => 'Resource Server Page Read Access',
                'api_id'             => $resource_server_api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/write',$current_realm),
                'short_description'  => 'Resource Server Write Access',
                'description'        => 'Resource Server Write Access',
                'api_id'             => $resource_server_api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/delete',$current_realm),
                'short_description'  => 'Resource Server Delete Access',
                'description'        => 'Resource Server Delete Access',
                'api_id'             => $resource_server_api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/update',$current_realm),
                'short_description'  => 'Resource Server Update Access',
                'description'        => 'Resource Server Update Access',
                'api_id'             => $resource_server_api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/update.status',$current_realm),
                'short_description'  => 'Resource Server Update Status',
                'description'        => 'Resource Server Update Status',
                'api_id'             => $resource_server_api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/resource-server/regenerate.secret',$current_realm),
                'short_description'  => 'Resource Server Regenerate Client Secret',
                'description'        => 'Resource Server Regenerate Client Secret',
                'api_id'             => $resource_server_api->id,
                'system'             => true,
            )
        );

        // api scopes


        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/read',$current_realm),
                'short_description'  => 'Get Api',
                'description'        => 'Get Api',
                'api_id'             => $api_api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/delete',$current_realm),
                'short_description'  => 'Deletes Api',
                'description'        => 'Deletes Api',
                'api_id'             => $api_api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/write',$current_realm),
                'short_description'  => 'Create Api',
                'description'        => 'Create Api',
                'api_id'             => $api_api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/update',$current_realm),
                'short_description'  => 'Update Api',
                'description'        => 'Update Api',
                'api_id'             => $api_api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/update.status',$current_realm),
                'short_description'  => 'Update Api Status',
                'description'        => 'Update Api Status',
                'api_id'             => $api_api->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api/read.page',$current_realm),
                'short_description'  => 'Get Api By Page',
                'description'        => 'Get Api By Page',
                'api_id'             => $api_api->id,
                'system'             => false,
            )
        );


        // api endpoint scopes


        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/read',$current_realm),
                'short_description'  => 'Get Api Endpoint',
                'description'        => 'Get Api Endpoint',
                'api_id'             => $api_api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/delete',$current_realm),
                'short_description'  => 'Deletes Api Endpoint',
                'description'        => 'Deletes Api Endpoint',
                'api_id'             => $api_api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/write',$current_realm),
                'short_description'  => 'Create Api Endpoint',
                'description'        => 'Create Api Endpoint',
                'api_id'             => $api_api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/update',$current_realm),
                'short_description'  => 'Update Api Endpoint',
                'description'        => 'Update Api Endpoint',
                'api_id'             => $api_api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/update.status',$current_realm),
                'short_description'  => 'Update Api Endpoint Status',
                'description'        => 'Update Api Endpoint Status',
                'api_id'             => $api_api_endpoint->id,
                'system'             => true,
            )
        );

        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/read.page',$current_realm),
                'short_description'  => 'Get Api Endpoints By Page',
                'description'        => 'Get Api Endpoints By Page',
                'api_id'             => $api_api_endpoint->id,
                'system'             => false,
            )
        );


        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/add.scope',$current_realm),
                'short_description'  => 'Add required scope to endpoint',
                'description'        => 'Add required scope to endpoint',
                'api_id'             => $api_api_endpoint->id,
                'system'             => false,
            )
        );


        ApiScope::create(
            array(
                'name'               => sprintf('%s/api-endpoint/remove.scope',$current_realm),
                'short_description'  => 'Remove required scope to endpoint',
                'description'        => 'Remove required scope to endpoint',
                'api_id'             => $api_api_endpoint->id,
                'system'             => false,
            )
        );

        //non system ones

        ApiScope::create(
            array(
                'name'               =>  'email',
                'short_description'  => 'This scope value requests access to the email and email_verified Claims. ',
                'description'        => 'This scope value requests access to the email and email_verified Claims. ',
                'system'             => false,
            )
        );

        ApiScope::create(
            array(
                'name'               =>  'profile',
                'short_description'  => 'This scope value requests access to the End-Users default profile Claims',
                'description'        => 'This scope value requests access to the End-Users default profile Claims',
                'system'             => false,
            )
        );

        ApiScope::create(
            array(
                'name'               =>  'Address',
                'short_description'  => 'This scope value requests access to the address Claim.',
                'description'        => 'This scope value requests access to the address Claim.',
                'system'             => false,
            )
        );

        //create endpoints

        $resource_server_api        = Api::where('name','=','resource-server')->first();
        $api_api                    = Api::where('name','=','api')->first();
        $api_api_endpoint           = Api::where('name','=','api-endpoint')->first();

        //resource server

        ApiEndpoint::create(
            array(
                'name'            => 'create-resource-server',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => 'api/v1/resource-server',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'get-resource-server',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => 'api/v1/resource-server/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-regenerate-secret',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => 'api/v1/resource-server/regenerate-client-secret/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-get-page',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => 'api/v1/resource-server/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-delete',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => 'api/v1/resource-server/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-update',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => 'api/v1/resource-server',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'resource-server-update-status',
                'active'          =>  true,
                'api_id'          => $resource_server_api->id,
                'route'           => 'api/v1/resource-server/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        // endpoints api endpoint

        ApiEndpoint::create(
            array(
                'name'            => 'get-api-endpoint',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint/{id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'delete-api-endpoint',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'create-api-endpoint',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-api-endpoint',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-api-endpoint-status',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'api-endpoint-get-page',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
            )
        );


        ApiEndpoint::create(
            array(
                'name'            => 'add-api-endpoint-scope',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint/scope/add/{id}/{scope_id}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'remove-api-endpoint-scope',
                'active'          =>  true,
                'api_id'          => $api_api_endpoint->id,
                'route'           => 'api/v1/api-endpoint/scope/remove/{id}/{scope_id}',
                'http_method'     => 'GET'
            )
        );


        // endpoints api

        ApiEndpoint::create(
            array(
                'name'            => 'get-api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => 'api/v1/api/{id}',
                'http_method'     => 'GET'
            )
        );


        ApiEndpoint::create(
            array(
                'name'            => 'delete-api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => 'api/v1/api/{id}',
                'http_method'     => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'create-api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => 'api/v1/api',
                'http_method'     => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-api',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => 'api/v1/api',
                'http_method'     => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'update-api-status',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => 'api/v1/api/status/{id}/{active}',
                'http_method'     => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name'            => 'api-get-page',
                'active'          =>  true,
                'api_id'          => $api_api->id,
                'route'           => 'api/v1/api/{page_nbr}/{page_size}',
                'http_method'     => 'GET'
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

        // create users and clients ...
        User::create(
            array(
                'identifier'=>'sebastian.marcet',
                'external_id'=>'smarcet@gmail.com',
                'last_login_date'=>gmdate("Y-m-d H:i:s", time())
            )
        );

        $user = User::where('external_id','=','smarcet@gmail.com')->first();

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

