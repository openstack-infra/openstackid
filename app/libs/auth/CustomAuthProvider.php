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
use openid\services\Registry;

class CustomAuthProvider implements UserProviderInterface
{

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
        //here we do the manuel join between 2 DB, (openid and SS db)
        $user = OpenIdUser::where('external_id', '=', $identifier)->first();
        $member = Member::where('Email', '=', $identifier)->first();
        if (!is_null($member) && !is_null($user)) {
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
        if (!isset($credentials['username']) || !isset($credentials['password']))
            throw new AuthenticationException("invalid crendentials");

        $identifier = $credentials['username'];
        $password = $credentials['password'];
        $user = OpenIdUser::where('external_id', '=', $identifier)->first();

        //check user status...
        if (!is_null($user) && ($user->lock || !$user->active))
            return null;

        //get SS member
        $member = Member::where('Email', '=', $identifier)->first();
        if (is_null($member))//member must exists
            return null;

        $user_service = Registry::getInstance()->get("openid\\services\\IUserService");

        $valid_password = $member->checkPassword($password);
        //if user does not exists, then create it
        if (is_null($user)) {
            //create user
            $user = new OpenIdUser();
            $user->external_id = $member->Email;
            $user->last_login_date = gmdate("Y-m-d H:i:s", time());
            $user->Save();
        }


        $user_name = $member->FirstName . "." . $member->Surname;
        //do association between user and member
        $user_service->associateUser($user->id, strtolower($user_name));

        $server_configuration = Registry::getInstance()->get("openid\\services\\IServerConfigurationService");

        if (!$valid_password) {
            //apply lock policy
            if ($user->login_failed_attempt < $server_configuration->getMaxFailedLoginAttempts())
                $user_service->updateFailedLoginAttempts($user->id);
            else {
                $user_service->lockUser($user->id);
            }
            $user = null;
        } else {
            //update user fields
            $user->last_login_date = gmdate("Y-m-d H:i:s", time());
            $user->login_failed_attempt = 0;
            $user->active = true;
            $user->lock = false;
            $user->Save();
        }
        //reload user...
        $user = OpenIdUser::where('external_id', '=', $identifier)->first();
        $user->setMember($member);
        return $user;
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
        if (!isset($credentials['username']) || !isset($credentials['password']))
            throw new AuthenticationException("invalid crendentials");

        $identifier = $credentials['username'];
        $password = $credentials['password'];
        $user = OpenIdUser::where('external_id', '=', $identifier)->first();

        if (is_null($user) || $user->lock || !$user->active)
            return false;

        $member = Member::where('Email', '=', $identifier)->first();

        return !is_null($member) ? $member->checkPassword($password) : false;
    }
}