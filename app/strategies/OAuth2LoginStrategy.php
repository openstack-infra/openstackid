<?php

namespace strategies;

use Auth;
use Redirect;
use View;

class OAuth2LoginStrategy implements  ILoginStrategy{

    public function  getLogin()
    {
        if (Auth::guest()) {
            return View::make("login");
        } else {
            return Redirect::action("UserController@getProfile");
        }
    }

    public function  postLogin()
    {
        return Redirect::action("OAuth2ProviderController@authorize");
    }

    public function  cancelLogin()
    {
        return Redirect::action("OAuth2ProviderController@authorize");
    }
}