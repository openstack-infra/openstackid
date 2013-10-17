<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 4:32 PM
 * To change this template use File | Settings | File Templates.
 */

namespace services;

use openid\handlers\IOpenIdAuthenticationStrategy;
use openid\requests\OpenIdAuthenticationRequest;
use \Redirect;
use openid\requests\contexts\RequestContext;

class AuthenticationStrategy implements IOpenIdAuthenticationStrategy{

    public function doLogin(OpenIdAuthenticationRequest $request,RequestContext $context)
    {
         return Redirect::action('UserController@getLogin')->with('context', $context);
    }

    public function doConsent(OpenIdAuthenticationRequest $request,RequestContext $context)
    {
        return Redirect::action('UserController@getConsent')->with('context', $context);;
    }
}