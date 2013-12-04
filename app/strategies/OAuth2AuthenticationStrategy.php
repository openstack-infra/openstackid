<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/4/13
 * Time: 11:32 AM
 */

namespace strategies;

use oauth2\requests\OAuth2AuthorizationRequest;
use oauth2\strategies\IOAuth2AuthenticationStrategy;
use Redirect;

class OAuth2AuthenticationStrategy implements IOAuth2AuthenticationStrategy {

    public function doLogin(OAuth2AuthorizationRequest $request)
    {
        return Redirect::action('UserController@getLogin');
    }

    public function doConsent(OAuth2AuthorizationRequest $request)
    {
        return Redirect::action('UserController@getConsent');
    }
}