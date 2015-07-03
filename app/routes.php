<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::pattern('id', '[0-9]+');
Route::pattern('uri_id', '[0-9]+');
Route::pattern('active', '(true|false)');
Route::pattern('hint', '(access-token|refresh-token)');
Route::pattern('scope_id', '[0-9]+');

Route::group(array("before" => "ssl"), function () {

    Route::get('/', "HomeController@index");
    Route::get('/discovery', "DiscoveryController@idp");
    /*
    * If the Claimed Identifier was not previously discovered by the Relying Party
    * (the "openid.identity" in the request was "http://specs.openid.net/auth/2.0/identifier_select"
    * or a different Identifier, or if the OP is sending an unsolicited positive assertion),
    * the Relying Party MUST perform discovery on the Claimed Identifier in
    * the response to make sure that the OP is authorized to make assertions about the Claimed Identifier.
    */
    Route::get("/{identifier}", "UserController@getIdentity");
    Route::get("/accounts/user/ud/{identifier}",
        "DiscoveryController@user")->where(array('identifier' => '[\d\w\.\#]+'));

    //op endpoint url
    Route::post('/accounts/openid2', 'OpenIdProviderController@endpoint');
    Route::get('/accounts/openid2', 'OpenIdProviderController@endpoint');

    //user interaction
    Route::get('/accounts/user/login', "UserController@getLogin");
    Route::post('/accounts/user/login', "UserController@postLogin");
    Route::get('/accounts/user/login/cancel', "UserController@cancelLogin");
});

//oauth2 endpoints
Route::group(array('prefix' => 'oauth2', 'before' => 'ssl|oauth2.enabled'), function () {
    //authorization endpoint
    Route::any('/auth', "OAuth2ProviderController@authorize");

    //token endpoint
    Route::group(array('prefix' => 'token'), function () {
        Route::post('/', "OAuth2ProviderController@token");
        Route::post('/revoke', "OAuth2ProviderController@revoke");
        Route::post('/introspection', "OAuth2ProviderController@introspection");
    });
});

Route::group(array("before" => array("ssl", "auth")), function () {
    Route::get('/accounts/user/consent', "UserController@getConsent");
    Route::post('/accounts/user/consent', "UserController@postConsent");
    Route::any("/accounts/user/logout", "UserController@logout");
    Route::any("/accounts/user/profile", "UserController@getProfile");
    Route::any("/accounts/user/profile/trusted_site/delete/{id}", "UserController@deleteTrustedSite");
    Route::post('/accounts/user/profile/update', 'UserController@postUserProfileOptions');
});

Route::group(array('prefix' => 'admin', 'before' => 'ssl|auth'), function () {
    //client admin UI
    Route::get('clients/edit/{id}',
        array('before' => 'oauth2.enabled|user.owns.client.policy', 'uses' => 'AdminController@editRegisteredClient'));
    Route::get('clients', array('before' => 'oauth2.enabled', 'uses' => 'AdminController@listOAuth2Clients'));

    Route::get('/grants', array('before' => 'oauth2.enabled', 'uses' => 'AdminController@editIssuedGrants'));
    //oauth2 server admin UI
    Route::group(array('before' => 'oauth2.enabled|oauth2.server.admin'), function () {

        Route::get('/resource-servers', 'AdminController@listResourceServers');
        Route::get('/resource-server/{id}', 'AdminController@editResourceServer');
        Route::get('/api/{id}', 'AdminController@editApi');
        Route::get('/scope/{id}', 'AdminController@editScope');
        Route::get('/endpoint/{id}', 'AdminController@editEndpoint');
        Route::get('/locked-clients', 'AdminController@listLockedClients');
        // server private keys
        Route::get('/private-keys', 'AdminController@listServerPrivateKeys');
    });

    Route::group(array('before' => 'openstackid.server.admin'), function () {
        Route::get('/locked-users', 'AdminController@listLockedUsers');
        Route::get('/server-config', 'AdminController@listServerConfig');
        Route::post('/server-config', 'AdminController@saveServerConfig');
        Route::get('/banned-ips', 'AdminController@listBannedIPs');
    });
});

//Admin Backend API
Route::group(array('prefix' => 'admin/api/v1', 'before' => 'ssl|auth'), function () {
    Route::group(array('prefix' => 'users'), function () {
        Route::delete('/{id}/locked',
            array('before' => 'openstackid.server.admin.json', 'uses' => 'UserApiController@unlock'));
        Route::delete('/{id}/token/{value}',
            array('before' => 'is.current.user', 'uses' => 'UserApiController@revokeToken'));
    });

    Route::group(array('prefix' => 'banned-ips', 'before' => 'openstackid.server.admin.json'), function () {
        Route::get('/{id}', "ApiBannedIPController@get");
        Route::get('/', "ApiBannedIPController@getByPage");
        Route::delete('/{id?}', "ApiBannedIPController@delete");
    });

    //client api
    Route::group(array('prefix' => 'clients'), function () {

        // public keys
        Route::post('/{id}/public_keys',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientPublicKeyApiController@create'));
        Route::get('/{id}/public_keys',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientPublicKeyApiController@getByPage'));
        Route::delete('/{id}/public_keys/{public_key_id}',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientPublicKeyApiController@delete'));
        Route::put('/{id}/public_keys/{public_key_id}',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientPublicKeyApiController@update'));

        Route::post('/', array('before' => 'is.current.user', 'uses' => 'ClientApiController@create'));
        Route::put('/', array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@update'));
        Route::get('/{id}', "ClientApiController@get");
        Route::get('/', array('before' => 'is.current.user', 'uses' => 'ClientApiController@getByPage'));
        Route::delete('/{id}', array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@delete'));
        //allowed redirect uris endpoints
        Route::get('/{id}/uris',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@getRegisteredUris'));
        Route::post('/{id}/uris',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@addAllowedRedirectUri'));
        Route::delete('/{id}/uris/{uri_id}',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@deleteClientAllowedUri'));

        //allowed origin endpoints endpoints
        Route::get('/{id}/origins',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@geAllowedOrigins'));
        Route::post('/{id}/origins',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@addAllowedOrigin'));
        Route::delete('/{id}/origins/{origin_id}',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@deleteClientAllowedOrigin'));

        Route::delete('/{id}/lock',
            array('before' => 'openstackid.server.admin.json', 'uses' => 'ClientApiController@unlock'));
        Route::put('/{id}/secret',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@regenerateClientSecret'));
        Route::put('/{id}/use-refresh-token',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@setRefreshTokenClient'));
        Route::put('/{id}/rotate-refresh-token',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@setRotateRefreshTokenPolicy'));
        Route::get('/{id}/access-token',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@getAccessTokens'));
        Route::get('/{id}/refresh-token',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@getRefreshTokens'));
        Route::delete('/{id}/token/{value}/{hint}',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@revokeToken'));
        Route::put('/{id}/scopes/{scope_id}',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@addAllowedScope'));
        Route::delete('/{id}/scopes/{scope_id}',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@removeAllowedScope'));

        Route::put('/{id}/active',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@activate'));
        Route::delete('/{id}/active',
            array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@deactivate'));

    });

    Route::group(array('prefix' => 'resource-servers', 'before' => 'oauth2.server.admin.json'), function () {
        Route::get('/{id}', "ApiResourceServerController@get");
        Route::get('/', "ApiResourceServerController@getByPage");
        Route::post('/', "ApiResourceServerController@create");
        Route::delete('/{id}', "ApiResourceServerController@delete");
        Route::put('/', "ApiResourceServerController@update");
        Route::put('/{id}/client-secret', "ApiResourceServerController@regenerateClientSecret");
        Route::put('/{id}/active', "ApiResourceServerController@activate");
        Route::delete('/{id}/active', "ApiResourceServerController@deactivate");
    });

    Route::group(array('prefix' => 'apis', 'before' => 'oauth2.server.admin.json'), function () {
        Route::get('/{id}', "ApiController@get");
        Route::get('/', "ApiController@getByPage");
        Route::post('/', "ApiController@create");
        Route::delete('/{id}', "ApiController@delete");
        Route::put('/', "ApiController@update");
        Route::put('/{id}/active', "ApiController@activate");
        Route::delete('/{id}/active', "ApiController@deactivate");
    });

    Route::group(array('prefix' => 'scopes', 'before' => 'oauth2.server.admin.json'), function () {
        Route::get('/{id}', "ApiScopeController@get");
        Route::get('/', "ApiScopeController@getByPage");
        Route::post('/', "ApiScopeController@create");
        Route::delete('/{id}', "ApiScopeController@delete");
        Route::put('/', "ApiScopeController@update");
        Route::put('/{id}/active', "ApiScopeController@activate");
        Route::delete('/{id}/active', "ApiScopeController@deactivate");
    });

    Route::group(array('prefix' => 'endpoints', 'before' => 'oauth2.server.admin.json'), function () {
        Route::get('/{id}', "ApiEndpointController@get");
        Route::get('/', "ApiEndpointController@getByPage");
        Route::post('/', "ApiEndpointController@create");
        Route::delete('/{id}', "ApiEndpointController@delete");
        Route::put('/', "ApiEndpointController@update");
        Route::put('/{id}/scope/{scope_id}', "ApiEndpointController@addRequiredScope");
        Route::delete('/{id}/scope/{scope_id}', "ApiEndpointController@removeRequiredScope");
        Route::put('/{id}/active', "ApiEndpointController@activate");
        Route::delete('/{id}/active', "ApiEndpointController@deactivate");
    });

    Route::group(array('prefix' => 'private-keys', 'before' => 'oauth2.server.admin.json'), function () {
        Route::get('/', "ServerPrivateKeyApiController@getByPage");
        Route::post('/', "ServerPrivateKeyApiController@create");
        Route::delete('/{id}', "ServerPrivateKeyApiController@delete");
        Route::put('/{id}', "ServerPrivateKeyApiController@update");
    });

});

//OAuth2 Protected API
Route::group(array(
    'prefix' => 'api/v1',
    'before' => 'ssl|oauth2.enabled|oauth2.protected.endpoint',
    'after' => ''
), function () {
    Route::group(array('prefix' => 'users'), function () {
        Route::get('/me', 'OAuth2UserApiController@me');
    });
});