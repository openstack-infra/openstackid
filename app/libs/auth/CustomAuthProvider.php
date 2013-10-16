<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 12:39 PM
 * To change this template use File | Settings | File Templates.
 */
namespace auth;

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;

class CustomAuthProvider implements UserProviderInterface{

    /**
     * @var UserService
     */
    private $userService;

    public function __construct()
    {

    }
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveById($identifier)
    {
        return null;
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $username = $credentials['username'];
        $password = $credentials['password'];
        return null;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Auth\UserInterface $user
     * @param  array $credentials
     * @return bool
     */
    public function validateCredentials(UserInterface $user, array $credentials)
    {
        $username = $credentials['username'];
        $password = $credentials['password'];
        return null;
    }
}