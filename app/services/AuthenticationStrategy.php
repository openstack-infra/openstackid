<?php

namespace services;

use openid\handlers\IOpenIdAuthenticationStrategy;
use openid\requests\contexts\RequestContext;
use openid\requests\OpenIdAuthenticationRequest;
use Redirect;

class AuthenticationStrategy implements IOpenIdAuthenticationStrategy
{

    public function doLogin(OpenIdAuthenticationRequest $request, RequestContext $context)
    {
        return Redirect::action('UserController@getLogin')->with('context', $context);
    }

    public function doConsent(OpenIdAuthenticationRequest $request, RequestContext $context)
    {
        return Redirect::action('UserController@getConsent')->with('context', $context);;
    }
}