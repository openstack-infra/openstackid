<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 12:40 PM
 * To change this template use File | Settings | File Templates.
 */


namespace auth;
use Illuminate\Auth\UserInterface;
use openid\model\IOpenIdUser;

class OpenIdUser extends Eloquent implements UserInterface , IOpenIdUser{

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        // TODO: Implement getAuthIdentifier() method.
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        // TODO: Implement getAuthPassword() method.
    }

    public function getIdentifier()
    {
        // TODO: Implement getIdentifier() method.
    }

    public function getEmail()
    {
        // TODO: Implement getEmail() method.
    }

    public function getFirstName()
    {
        // TODO: Implement getFirstName() method.
    }

    public function getLastName()
    {
        // TODO: Implement getLastName() method.
    }

    public function getFullName()
    {
        // TODO: Implement getFullName() method.
    }

    public function getNickName()
    {
        // TODO: Implement getNickName() method.
    }

    public function getGender()
    {
        // TODO: Implement getGender() method.
    }

    public function getCountry()
    {
        // TODO: Implement getCountry() method.
    }

    public function getLanguage()
    {
        // TODO: Implement getLanguage() method.
    }

    public function getTimeZone()
    {
        // TODO: Implement getTimeZone() method.
    }

    public function getDateOfBirth()
    {
        // TODO: Implement getDateOfBirth() method.
    }
}