<?php
use openid\exceptions\InvalidOpenIdMessageException;
use openid\requests\OpenIdAuthenticationRequest;
use openid\services\OpenIdServiceCatalog;
use utils\services\Registry;
use utils\services\UtilsServiceCatalog;
use oauth2\services\OAuth2ServiceCatalog;
use oauth2\exceptions\InvalidAuthorizationRequestException;
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
App::before(function ($request) {
    try {
        //checkpoint security pattern entry point
        $checkpoint_service = Registry::getInstance()->get(UtilsServiceCatalog::CheckPointService);
        if (!$checkpoint_service->check()) {
            return View::make('404');
        }
    } catch (Exception $ex) {
        Log::error($ex);
        return View::make('404');
    }
});


App::after(function ($request, $response) {
    //
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
    if ($redirect = Session::get('url.intended')) {
        Session::forget('url.intended');
        return Redirect::to($redirect);
    }
});


Route::filter('auth.server.admin.json',function(){
    if (Auth::guest()) {
        return Response::json(array('error' => 'you are not allowed to perform this operation'));
    }
    if(Auth::user()->IsServerAdmin()){
        return Response::json(array('error' => 'you are not allowed to perform this operation'));
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


Route::filter("openid.needs.auth.request", function () {

    $memento_service = App::make(OpenIdServiceCatalog::MementoService);
    $openid_message = $memento_service->getCurrentRequest();

    if ($openid_message == null || !$openid_message->isValid())
        throw new InvalidOpenIdMessageException();

    $auth_request = new OpenIdAuthenticationRequest($openid_message);
    if (!$auth_request->isValid())
        throw new InvalidOpenIdMessageException();
});

Route::filter("openid.save.request", function () {

    $memento_service = App::make(OpenIdServiceCatalog::MementoService);
    $memento_service->saveCurrentRequest();

});

Route::filter("oauth2.save.request", function () {

    $memento_service = App::make(OAuth2ServiceCatalog::MementoService);
    $memento_service->saveCurrentAuthorizationRequest();
});

Route::filter("oauth2.needs.auth.request", function () {

    $memento_service = App::make(OAuth2ServiceCatalog::MementoService);
    $oauth2_message = $memento_service->getCurrentAuthorizationRequest();

    if ($oauth2_message == null || !$oauth2_message->isValid())
        throw new InvalidAuthorizationRequestException();

});

Route::filter("ssl", function () {
    if (!Request::secure()) {
        $openid_memento_service = Registry::getInstance()->get(OpenIdServiceCatalog::MementoService);
        $openid_memento_service->saveCurrentRequest();

        $oauth2_memento_service = App::make(OAuth2ServiceCatalog::MementoService);
        $oauth2_memento_service->saveCurrentAuthorizationRequest();

        return Redirect::secure(Request::getRequestUri());
    }
});

Route::filter('user.owns.client.policy',function($route, $request){
    try{
        $authentication_service = App::make(UtilsServiceCatalog::AuthenticationService);
        $client_service         = App::make(OAuth2ServiceCatalog::ClientService);
        $client_id              = $route->getParameter('id');
        $client                 = $client_service->getClientByIdentifier($client_id);
        $user                   = $authentication_service->getCurrentUser();
        if (is_null($client) || $client->getUserId() !== $user->getId())
            throw new Exception('invalid client id for current user');

    } catch (Exception $ex) {
        Log::error($ex);
        return Response::json(array('error' => 'operation not allowed.'), 400);
    }
});




// filter to protect an api endpoint with oauth2
Route::filter('oauth2.protected.endpoint','OAuth2BearerAccessTokenRequestValidator');