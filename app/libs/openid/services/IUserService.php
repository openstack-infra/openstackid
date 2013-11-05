<?php

namespace openid\services;


interface IUserService
{

    public function associateUser($id, $proposed_username);

    public function updateLastLoginDate($identifier);

    public function updateFailedLoginAttempts($identifier);

    public function lockUser($identifier);

    public function unlockUser($identifier);

    public function activateUser($identifier);

    public function deActivateUser($identifier);

    public function saveProfileInfo($identifier, $show_pic, $show_full_name, $show_email);
}