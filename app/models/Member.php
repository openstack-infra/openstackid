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
    public $timestamps     = false;

    public function checkPassword($password)
    {
        $hash = AuthHelper::encrypt_password($password, $this->Salt, $this->PasswordEncryption);
        $res  = AuthHelper::compare($this->Password, $hash , $this->PasswordEncryption);
        return $res;
    }

    public function groups()
    {

        return $this->belongsToMany('Group', 'Group_Members', 'MemberID', 'GroupID');
    }

    /**
     * @return bool
     */
    public function canLogin()
    {
        $attr = $this->getAttributes();
        if(isset($attr['Active']))
        {
            return (bool)$attr['Active'];
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isEmailVerified()
    {
        $attr = $this->getAttributes();
        if(isset($attr['EmailVerified']))
        {
            return (bool)$attr['EmailVerified'];
        }
        return false;
    }
}