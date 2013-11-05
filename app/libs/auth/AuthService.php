<?php

namespace auth;

use Auth;
use openid\services\AuthorizationResponse_;
use openid\services\IAuthService;
use Session;

class AuthService implements IAuthService
{

    /**
     * @return mixed
     */
    public function isUserLogged()
    {
        return Auth::check();
    }

    /**
     * @return mixed
     */
    public function getCurrentUser()
    {
        return Auth::user();
    }

    /**
     * @param $username
     * @param $password
     * @param $remember_me
     * @return mixed
     */
    public function Login($username, $password, $remember_me)
    {
        return Auth::attempt(array('username' => $username, 'password' => $password), $remember_me);
    }

    public function logout()
    {
        Auth::logout();
    }

    /**
     * @return AuthorizationResponse_*
     */
    public function getUserAuthorizationResponse()
    {
        if (Session::has("openid.authorization.response")) {
            $value = Session::get("openid.authorization.response");
            Session::remove('openid.authorization.response');
            return $value;
        }
        return IAuthService::AuthorizationResponse_None;
    }

    public function setUserAuthorizationResponse($auth_response)
    {
        //todo : check valid response
        Session::set("openid.authorization.response", $auth_response);
    }

    public function getUserByOpenId($openid)
    {
        $user = OpenIdUser::where('identifier', '=', $openid)->first();
        return $user;
    }

    public function getUserByUsername($username)
    {
        $user = OpenIdUser::where('external_id', '=', $username)->first();
        return $user;
    }
}