<?php
use openid\exceptions\InvalidOpenIdMessageException;
use openid\requests\OpenIdAuthenticationRequest;
use openid\services\OpenIdServiceCatalog;
use utils\services\ServiceLocator;
use utils\services\UtilsServiceCatalog;
use oauth2\services\OAuth2ServiceCatalog;
use oauth2\exceptions\InvalidAuthorizationRequestException;
use oauth2\strategies\ClientAuthContextValidatorFactory;
use services\oauth2\HttpIClientJWKSetReader;

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

//SAP (single access point)
App::before(function($request){

    ClientAuthContextValidatorFactory::setTokenEndpointUrl
    (
        URL::action("OAuth2ProviderController@token")
    );

    ClientAuthContextValidatorFactory::setJWKSetReader
    (
        App::make('oauth2\services\IClientJWKSetReader')
    );

    try
    {
        //checkpoint security pattern entry point
        $checkpoint_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::CheckPointService);
        if (!$checkpoint_service->check())
        {
            return View::make('404');
        }
    }
    catch (Exception $ex)
    {
        Log::error($ex);
        return View::make('404');
    }

    $cors = ServiceLocator::getInstance()->getService('CORSMiddleware');
    if($response = $cors->verifyRequest($request))
        return $response;
});

App::after(function($request, $response){
    // https://www.owasp.org/index.php/List_of_useful_HTTP_headers
    $response->headers->set('X-content-type-options','nosniff');
    $response->headers->set('X-xss-protection','1; mode=block');
    //cache
    $response->headers->set('pragma','no-cache');
    $response->headers->set('Expires','-1');
    $response->headers->set('cache-control','no-store, must-revalidate, no-cache');
    $cors = ServiceLocator::getInstance()->getService('CORSMiddleware');
    $cors->modifyResponse($request, $response);
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function () {
    if (Auth::guest()) {
        Session::put('url.intended', URL::full());
        return Redirect::action('HomeController@index');
    }
    $redirect = Session::get('url.intended');
    if (!empty($redirect)) {
        Session::forget('url.intended');
        return Redirect::to($redirect);
    }
});

Route::filter('auth.basic', function () {
    return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function () {
    if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function () {
    if (Session::token() != Input::get('_token')) {
        throw new Illuminate\Session\TokenMismatchException;
    }
});

Route::filter('ajax', function()
{
    if (!Request::ajax()) App::abort(404);
});

Route::filter("ssl", function () {
    if ((!Request::secure()) && (ServerConfigurationService::getConfigValue("SSL.Enable"))) {
        return Redirect::secure(Request::getRequestUri());
    }
});

Route::filter("oauth2.enabled",function(){
    if(!ServerConfigurationService::getConfigValue("OAuth2.Enable")){
        return View::make('404');
    }
});

Route::filter('user.owns.client.policy',function($route, $request){
    try{
        $authentication_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::AuthenticationService);
        $client_service         = ServiceLocator::getInstance()->getService(OAuth2ServiceCatalog::ClientService);
        $client_id              = $route->getParameter('id');

        if(is_null($client_id))
            $client_id          = $route->getParameter('client_id');

        if(is_null($client_id))
            $client_id          =Input::get('client_id',null);;

        $client                 = $client_service->getClientByIdentifier($client_id);
        $user                   = $authentication_service->getCurrentUser();
        if (is_null($client) || intval($client->getUserId()) !== intval($user->getId()))
            throw new Exception('invalid client id for current user');

    } catch (Exception $ex) {
        Log::error($ex);
        return Response::json(array('error' => 'operation not allowed.'), 400);
    }
});

Route::filter('is.current.user',function($route, $request){
    try{
        $authentication_service = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::AuthenticationService);
        $used_id                = Input::get('user_id',null);

        if(is_null($used_id))
            $used_id            = Input::get('id',null);

        if(is_null($used_id))
            $used_id =  $route->getParameter('user_id');

        if(is_null($used_id))
            $used_id =  $route->getParameter('id');

        $user                   = $authentication_service->getCurrentUser();
        if (is_null($used_id) || intval($used_id) !== intval($user->getId()))
            throw new Exception(sprintf('user id %s does not match with current user id %s',$used_id,$user->getId()));

    } catch (Exception $ex) {
        Log::error($ex);
        return Response::json(array('error' => 'operation not allowed.'), 400);
    }
});


// filter to protect an api endpoint with oauth2

Route::filter('oauth2.protected.endpoint','OAuth2BearerAccessTokenRequestValidator');

//oauth2 server admin filter

Route::filter('oauth2.server.admin.json',function(){
    if (Auth::guest()) {
        return Response::json(array('error' => 'you are not allowed to perform this operation'));
    }
    if(!Auth::user()->isOAuth2ServerAdmin()){
        return Response::json(array('error' => 'you are not allowed to perform this operation'));
    }
});


Route::filter('oauth2.server.admin',function(){
    if (Auth::guest()) {
        return View::make('404');
    }
    if(!Auth::user()->isOAuth2ServerAdmin()){
        return View::make('404');
    }
});


//openstackid server admin

Route::filter('openstackid.server.admin.json',function(){
    if (Auth::guest()) {
        return Response::json(array('error' => 'you are not allowed to perform this operation'));
    }
    if(!Auth::user()->isOpenstackIdAdmin()){
        return Response::json(array('error' => 'you are not allowed to perform this operation'));
    }
});


Route::filter('openstackid.server.admin',function(){
    if (Auth::guest()) {
        return View::make('404');
    }
    if(!Auth::user()->isOpenstackIdAdmin()){
        return View::make('404');
    }
});