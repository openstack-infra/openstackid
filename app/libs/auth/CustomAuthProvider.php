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
use auth\exceptions\AuthenticationException;
use \Member;
use \Zend\Crypt\Hash;

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
        $user = OpenIdUser::where('external_id', '=', $identifier)->first();
        $member = Member::where('Email', '=', $identifier)->first();
        if(!is_null($member) && !is_null($user)){
            $user->setMember($member);
            return $user;
        }
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
        if(!isset($credentials['username']) ||  !isset($credentials['password']))
            throw new AuthenticationException("invalid crendentials");
        $identifier = $credentials['username'];
        $password = $credentials['password'];
        $user = OpenIdUser::where('external_id', '=', $identifier)->first();
        $member = Member::where('Email', '=', $identifier)->first();
        if(!is_null($member) && $member->checkPassword($password)){
            if(is_null($user)){
                //create user
                $user = new OpenIdUser();
                $user->external_id = $member->Email;
                $user->active = true;
                $user->identifier = Hash::compute("sha1",$user->external_id);
                $user->Save();
            }
            $user->setMember($member);
            return $user;
        }
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
        if(!isset($credentials['username']) ||  !isset($credentials['password']))
            throw new AuthenticationException("invalid crendentials");
        $identifier = $credentials['username'];
        $password = $credentials['password'];
        $member = Member::where('Email', '=', $identifier)->first();
        return  $member->checkPassword($password);
    }
}