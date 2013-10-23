<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/22/13
 * Time: 5:04 PM
 * To change this template use File | Settings | File Templates.
 */

namespace services;
use openid\services\IUserService;
use auth\OpenIdUser;

class UserService implements IUserService{

    public function associateUser($id, $proposed_username)
    {
        $user = OpenIdUser::where('id', '=', $id)->first();
        if(!empty($user->identifier)) return $user->identifier;
        if(!is_null($user)){
            \DB::transaction(function() use ($id,$proposed_username)
            {
                $done = false;
                $fragment_nbr = 1;
                do{
                    $old_user = \DB::table('openid_users')->where('identifier', '=', $proposed_username)->first();
                    if(is_null($old_user)){
                        \DB::table('openid_users')->where('id', '=', $id)->update(array('identifier' => $proposed_username));
                        $done = true;
                    }
                    else{
                        $proposed_username = $proposed_username."#".$fragment_nbr;
                        $fragment_nbr++;
                    }

                }while(!$done);
                return $proposed_username;
            });
        }
        return false;
    }

    public function updateLastLoginDate($identifier)
    {
        $user = OpenIdUser::where('id', '=', $identifier)->first();
        if(!is_null($user)){
            \DB::transaction(function() use ($identifier)
            {
                \DB::table('openid_users')->where('id', '=', $identifier)->update(array('last_login_date' => gmdate("Y-m-d H:i:s", time())));
            });
        }
    }

    public function updateFailedLoginAttempts($identifier)
    {
        $user = OpenIdUser::where('id', '=', $identifier)->first();
        if(!is_null($user)){
            $attempts = $user->login_failed_attempt;
            ++$attempts;
            \DB::transaction(function() use ($identifier,$attempts)
            {
                \DB::table('openid_users')->where('id', '=', $identifier)->update(array('login_failed_attempt' => $attempts));
            });
        }
    }

    public function lockUser($identifier)
    {
        $user = OpenIdUser::where('id', '=', $identifier)->first();
        if(!is_null($user)){
            \DB::transaction(function() use ($identifier)
            {
                \DB::table('openid_users')->where('id', '=', $identifier)->update(array('lock' => 1));
            });
        }
    }

    public function unlockUser($identifier)
    {
        $user = OpenIdUser::where('id', '=', $identifier)->first();
        if(!is_null($user)){
            \DB::transaction(function() use ($identifier)
            {
                \DB::table('openid_users')->where('id', '=', $identifier)->update(array('lock' => 0));
            });
        }
    }

    public function activateUser($identifier)
    {
        $user = OpenIdUser::where('id', '=', $identifier)->first();
        if(!is_null($user)){
            \DB::transaction(function() use ($identifier)
            {
                \DB::table('openid_users')->where('id', '=', $identifier)->update(array('active' => 1));
            });
        }
    }

    public function deActivateUser($identifier)
    {
        $user = OpenIdUser::where('id', '=', $identifier)->first();
        if(!is_null($user)){
            \DB::transaction(function() use ($identifier)
            {
                \DB::table('openid_users')->where('id', '=', $identifier)->update(array('active' => 0));
            });
        }
    }
}