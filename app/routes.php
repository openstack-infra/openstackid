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
Route::pattern('active', '(true|false|1|0)');
Route::pattern('hint', '(access-token|refresh-token)');
Route::pattern('scope_id', '[0-9]+');
Route::pattern('page_nbr', '[0-9]+');
Route::pattern('page_size', '[0-9]+');
//Route::pattern('filter', '((\W){1,}(=|<|>|<>){1,1}(\W){1,})*');


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
    Route::get("/accounts/user/ud/{identifier}", "DiscoveryController@user")->where(array('identifier' => '[\d\w\.\#]+'));

    //op endpoint url
    Route::post('/accounts/openid2', 'OpenIdProviderController@op_endpoint');
    Route::get('/accounts/openid2', 'OpenIdProviderController@op_endpoint');

    //user interaction
    Route::get('/accounts/user/login', "UserController@getLogin");
    Route::post('/accounts/user/login', "UserController@postLogin");
    Route::get('/accounts/user/login/cancel', "UserController@cancelLogin");
});

//oauth2 endpoints
Route::group(array('prefix' => 'oauth2', 'before' => 'ssl'), function()
{
    //authorization endpoint
    Route::any('/auth',"OAuth2ProviderController@authorize");

    //token endpoint
    Route::group(array('prefix' => 'token'), function(){
        Route::post('/',"OAuth2ProviderController@token");
        Route::post('/revoke',"OAuth2ProviderController@revoke");
        Route::post('/introspection',"OAuth2ProviderController@introspection");
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

Route::group(array('prefix' => 'admin','before' => 'ssl|auth'), function(){
    //client admin UI
    Route::get('clients/edit/{id}',array('before' => 'user.owns.client.policy', 'uses' => 'AdminController@getEditRegisteredClient'));
    Route::get('clients',array('uses' => 'AdminController@listOAuth2Clients'));

    Route::get('/grants','AdminController@editIssuedGrants');
    Route::get('/grants/revoke/{value}','AdminController@revokeToken');

    //server admin UI
    Route::group(array('before' => 'auth.server.admin'), function(){
        Route::get('/resource-servers','AdminController@listResourceServers');
        Route::get('/resource-server/{id}','AdminController@editResourceServer');
        Route::get('/api/{id}','AdminController@editApi');
        Route::get('/scope/{id}','AdminController@editScope');
        Route::get('/endpoint/{id}','AdminController@editEndpoint');
    });

});


//Admin Backend API

Route::group(array('prefix' => 'admin/api/v1', 'before' => 'ssl|auth'), function()
{
    //client api
    Route::group(array('prefix' => 'clients'), function(){

        Route::post('/', array('before' => 'is.current.user', 'uses' => 'ClientApiController@create'));
        Route::get('/',array('before' => 'is.current.user', 'uses' => 'ClientApiController@getByPage'));
        Route::delete('/{id}',array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@delete'));

        Route::group(array('prefix' => 'uris','before' => 'user.owns.client.policy'), function(){
            Route::get('/{id}',"ClientApiController@getRegisteredUris");
            Route::post('/{id}',"ClientApiController@addAllowedRedirectUri");
            Route::delete('/{id}/{uri_id}',"ClientApiController@deleteClientAllowedUri");
        });

        Route::put('/{id}/secret',array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@regenerateClientSecret'));
        Route::put('/{id}/use-refresh-token',array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@setRefreshTokenClient'));
        Route::put('/{id}/rotate-refresh-token',array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@setRotateRefreshTokenPolicy'));
        Route::get('/{id}/access-token',array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@getAccessTokens'));
        Route::get('/{id}/refresh-token',array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@getRefreshTokens'));
        Route::delete('/{id}/token/{value}/{hint}',array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@revokeToken'));
        Route::put('/{id}/status/{active}',array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@updateStatus'));
        Route::post('/{id}/set-scopes',array('before' => 'user.owns.client.policy', 'uses' => 'ClientApiController@addAllowedScope'));

    });

    Route::group(array('prefix' => 'resource-servers', 'before' => 'auth.server.admin.json'), function(){
        Route::delete('/{id}',"ApiResourceServerController@delete");
        Route::post('/',"ApiResourceServerController@create");
        Route::put('/',"ApiResourceServerController@update");
        Route::get('/',"ApiResourceServerController@getByPage");
    });

    Route::group(array('prefix' => 'apis', 'before' => 'auth.server.admin.json'), function(){
        Route::delete('/{id}',"ApiController@delete");
        Route::post('/',"ApiController@create");
        Route::get('/',"ApiController@getByPage");
        Route::put('/',"ApiController@update");
        Route::put('/{id}/status/{active}',"ApiController@updateStatus");
    });

    Route::group(array('prefix' => 'scopes', 'before' => 'auth.server.admin.json'), function(){
        Route::delete('/{id}',"ApiScopeController@delete");
        Route::post('/',"ApiScopeController@create");
        Route::get('/',"ApiScopeController@getByPage");
        Route::put('/',"ApiScopeController@update");
        Route::put('/{id}/status/{active}',"ApiScopeController@updateStatus");
    });

    Route::group(array('prefix' => 'endpoints', 'before' => 'auth.server.admin.json'), function(){
        Route::delete('/{id}',"ApiEndpointController@delete");
        Route::get('/',"ApiEndpointController@getByPage");
        Route::post('/',"ApiEndpointController@create");
        Route::put('/',"ApiEndpointController@update");
        Route::put('/{id}/status/{active}',"ApiEndpointController@updateStatus");
        Route::put('/{id}/scope/{scope_id}',"ApiEndpointController@addRequiredScope");
        Route::delete('/{id}/scope/{scope_id}',"ApiEndpointController@removeRequiredScope");
    });
});


//OAuth2 Protected API

Route::group(array('prefix' => 'api/v1', 'before' => 'ssl|oauth2.protected.endpoint'), function()
{
    //resource server api
    Route::group(array('prefix' => 'resource-servers'), function(){

        Route::post('/',"OAuth2ProtectedApiResourceServerController@create");
        Route::put('/{id}/client-secret',"OAuth2ProtectedApiResourceServerController@regenerateClientSecret");
        Route::get('/{id}',"OAuth2ProtectedApiResourceServerController@get");
        Route::get('/',"OAuth2ProtectedApiResourceServerController@getByPage");
        Route::delete('/{id}',"OAuth2ProtectedApiResourceServerController@delete");
        Route::put('/',"OAuth2ProtectedApiResourceServerController@update");
        Route::put('/{id}/status/{active}',"OAuth2ProtectedApiResourceServerController@updateStatus");
    });

    // api
    Route::group(array('prefix' => 'apis'), function(){
        Route::get('/{id}',"OAuth2ProtectedApiController@get");
        Route::get('/',"OAuth2ProtectedApiController@getByPage");
        Route::delete('/{id}',"OAuth2ProtectedApiController@delete");
        Route::post('/',"OAuth2ProtectedApiController@create");
        Route::put('/',"OAuth2ProtectedApiController@update");
        Route::put('/{id}/status/{active}',"OAuth2ProtectedApiController@updateStatus");
    });

    // api endpoints
    Route::group(array('prefix' => 'api-endpoints'), function(){
        Route::get('/{id}',"OAuth2ProtectedApiEndpointController@get");
        Route::get('/',"OAuth2ProtectedApiEndpointController@getByPage");
        Route::post('/',"OAuth2ProtectedApiEndpointController@create");
        Route::put('/',"OAuth2ProtectedApiEndpointController@update");
        Route::delete('/{id}',"OAuth2ProtectedApiEndpointController@delete");
        Route::put('/{id}/status/{active}',"OAuth2ProtectedApiEndpointController@updateStatus");
        Route::put('/{id}/scope/{scope_id}',"OAuth2ProtectedApiEndpointController@addRequiredScope");
        Route::delete('/{id}/scope/{scope_id}',"OAuth2ProtectedApiEndpointController@removeRequiredScope");
    });

    //scopes endpoints
    Route::group(array('prefix' => 'api-scopes'), function(){
        Route::get('/',"OAuth2ProtectedApiScopeController@getByPage");
        Route::get('/{id}',"OAuth2ProtectedApiScopeController@get");
        Route::post('/',"OAuth2ProtectedApiScopeController@create");
        Route::put('/',"OAuth2ProtectedApiScopeController@update");
        Route::delete('/{id}',"OAuth2ProtectedApiScopeController@delete");
        Route::put('/{id}/status/{active}',"OAuth2ProtectedApiScopeController@updateStatus");
    });

});