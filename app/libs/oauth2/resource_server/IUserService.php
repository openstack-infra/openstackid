<?php

namespace oauth2\resource_server;

/**
 * Interface IUserService
 * @package oauth2\resource_server
 */
interface IUserService
{
    /**
     * This scope value requests access to the End-User's default profile Claims, which are:
     * name, family_name, given_name, middle_name, nickname, preferred_username, profile, picture,
     * website, gender, birthdate, zoneinfo, locale, and updated_at.
     */
    const UserProfileScope_Profile   = 'profile';
    /**
     * This scope value requests access to the email and email_verified Claims.
     */
    const UserProfileScope_Email     = 'email';
    /**
    * This scope value requests access to the address Claim.
    */
    const UserProfileScope_Address   = 'address';

    public function getCurrentUserInfo();
}