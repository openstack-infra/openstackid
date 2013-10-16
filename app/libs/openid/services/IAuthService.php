<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 4:39 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\services;


interface IAuthService {
    /**
     * @return mixed
     */
    public function isUserLogged();

    /**
     * @return mixed
     */
    public function getCurrentUser();

    /**
     * @param $username
     * @param $password
     * @return mixed
     */
    public function Login($username,$password);

    /**
     * @return mixed
     */
    public function isUserAuthorized();

    public function logout();
}