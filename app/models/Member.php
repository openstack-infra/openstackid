<?php

use auth\AuthHelper;

/**
 * Class Member
 */
class Member extends Eloquent
{

    protected $table = 'Member';
    //external os members db (SS)
    protected $connection = 'os_members';

    public function checkPassword($password)
    {
        $digest = AuthHelper::encrypt_password($password, $this->Salt, $this->PasswordEncryption);
        $res    = AuthHelper::compare($this->Password, $digest);
        return $res;
    }

    public function groups(){

        return $this->belongsToMany('Group', 'Group_Members', 'MemberID', 'GroupID');
    }
}