<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 12:46 PM
 * To change this template use File | Settings | File Templates.
 */

namespace auth;
use openid\services\AuthorizationResponse_;
use openid\services\IAuthService;
use \Auth;
use \Session;

class AuthService implements  IAuthService {

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
     * @return mixed
     */
    public function Login($username, $password)
    {
        return Auth::attempt(array('username' => $username, 'password' => $password), true);
    }

    public function logout(){
        Auth::logout();
    }

    /**
     * @return AuthorizationResponse_*
     */
    public function getUserAuthorizationResponse()
    {
        return Session::get("openid.authorization.response");
    }
}