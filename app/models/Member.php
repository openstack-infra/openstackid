<?php

use auth\AuthHelper;

class Member extends Eloquent
{

    protected $table = 'Member';
    protected $connection = 'mysql_external';

    public function checkPassword($password)
    {
        $digest = AuthHelper::encrypt_password($password, $this->Salt, $this->PasswordEncryption);
        $res = AuthHelper::compare($this->Password, $digest);
        return $res;
    }
}