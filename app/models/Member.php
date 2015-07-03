<?php

use auth\AuthHelper;
use utils\model\BaseModelEloquent;

/**
 * Class Member
 */
class Member extends BaseModelEloquent
{

    protected $primaryKey ='ID';
    protected $table      = 'Member';
    //external os members db (SS)
    protected $connection = 'os_members';

    //no timestamps
    public $timestamps = false;

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