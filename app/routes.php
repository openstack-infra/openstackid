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

    //oauth2 endpoint

    Route::any('/oauth2/auth',"OAuth2ProviderController@authorize");
    Route::post('/oauth2/token',"OAuth2ProviderController@token");
});

Route::group(array("before" => array("ssl", "auth")), function () {
    Route::get('/accounts/user/consent', "UserController@getConsent");
    Route::post('/accounts/user/consent', "UserController@postConsent");
    Route::any("/accounts/user/logout", "UserController@logout");
    Route::any("/accounts/user/profile", "UserController@getProfile");
    Route::any("/accounts/user/profile/trusted_site/delete/{id}", "UserController@get_deleteTrustedSite");
    Route::post('/accounts/user/profile/update', 'UserController@postUserProfileOptions');
    Route::get('/accounts/user/profile/clients/edit/{id}','UserController@getEditRegisteredClient');
    Route::get('/accounts/user/profile/clients/delete/{id}','UserController@getDeleteRegisteredClient');
    Route::post('/accounts/user/profile/clients/add','UserController@postAddRegisteredClient');
    Route::get('/accounts/user/profile/clients/regenerate/clientsecret/{id}','UserController@getRegenerateClientSecret');
    Route::post('/accounts/user/profile/clients/redirect_uri/add/{id}','UserController@postAddAllowedRedirectUri');
    Route::get('/accounts/user/profile/clients/redirect_uri/list/{id}','UserController@getRegisteredClientUris');
    Route::get('/accounts/user/profile/clients/redirect_uri/delete/{id}/{uri_id}','UserController@getDeleteClientAllowedUri');
    Route::post('/accounts/user/profile/clients/scope/add/{id}','UserController@postAddAllowedScope');
    Route::post('/accounts/user/profile/clients/activate','UserController@postActivateClient');
});
