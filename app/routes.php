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

Route::get('/', "HomeController@index");
Route::get('/discovery', "DiscoveryController@idp");
//op endpoint url
Route::post('/accounts/openid/v2','OpenIdProviderController@op_endpoint');
Route::get('/accounts/openid/v2','OpenIdProviderController@op_endpoint');

//user interaction
Route::get('/accounts/user/login',"UserController@getLogin");

Route::post('/accounts/user/login',"UserController@postLogin");


Route::get('/accounts/user/consent',"UserController@getConsent");

Route::post('/accounts/user/consent',"UserController@postConsent");
