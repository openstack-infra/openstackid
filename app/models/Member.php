<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/18/13
 * Time: 11:11 AM
 * To change this template use File | Settings | File Templates.
 */
use auth\AuthHelper;

class Member extends Eloquent {

    protected $table = 'Member';
    protected $connection='mysql_external';

    public function checkPassword($password){
        $digest = AuthHelper::encrypt_password($password,$this->Salt,$this->PasswordEncryption);
        $res = AuthHelper::compare($this->Password , $digest);
        return $res;
    }
}