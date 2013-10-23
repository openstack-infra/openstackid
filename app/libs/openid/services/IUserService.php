<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/22/13
 * Time: 4:58 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\services;


interface IUserService {
    public function associateUser($id,$proposed_username);
    public function updateLastLoginDate($identifier);
    public function updateFailedLoginAttempts($identifier);
    public function lockUser($identifier);
    public function unlockUser($identifier);
    public function activateUser($identifier);
    public function deActivateUser($identifier);
}