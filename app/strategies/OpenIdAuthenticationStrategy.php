<?php

namespace strategies;

use Redirect;
use Session;
use Illuminate\Http\RedirectResponse;
use openid\handlers\IOpenIdAuthenticationStrategy;
use openid\requests\contexts\RequestContext;
use openid\requests\OpenIdAuthenticationRequest;

/**
 * Class OpenIdAuthenticationStrategy
 * @package services\openid
 */
final class OpenIdAuthenticationStrategy implements IOpenIdAuthenticationStrategy
{

    /**
     * @param OpenIdAuthenticationRequest $request
     * @param RequestContext $context
     * @return RedirectResponse
     */
    public function doLogin(OpenIdAuthenticationRequest $request, RequestContext $context)
    {
        Session::put('openid.auth.context', $context);
        Session::save();
        return Redirect::action('UserController@getLogin');
    }

    /**
     * @param OpenIdAuthenticationRequest $request
     * @param RequestContext $context
     * @return RedirectResponse
     */
    public function doConsent(OpenIdAuthenticationRequest $request, RequestContext $context)
    {
        Session::put('openid.auth.context', $context);
        Session::save();
        return Redirect::action('UserController@getConsent');
    }
}