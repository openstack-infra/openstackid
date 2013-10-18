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


class OpenIdUser extends \Eloquent implements UserInterface , IOpenIdUser{

    protected $table = 'openid_users';
    private $member;

    public function setMember($member){
        $this->member=$member;
    }
    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        if(is_null($this->member)){
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->external_id;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        if(is_null($this->member)){
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->member->Password;
    }

    public function getIdentifier()
    {
        if(is_null($this->member)){
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->identifier;
    }

    public function getEmail()
    {
        $this->external_id;
    }

    public function getFirstName()
    {
        if(is_null($this->member)){
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->member->FirstName;
    }

    public function getLastName()
    {
        if(is_null($this->member)){
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->member->Surname;
    }

    public function getFullName()
    {
        return $this->getFirstName()." ". $this->getLastName();
    }

    public function getNickName()
    {
        return $this->getFullName;
    }

    public function getGender()
    {
        if(is_null($this->member)){
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return "";
    }

    public function getCountry()
    {
        if(is_null($this->member)){
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->member->Country;
    }

    public function getLanguage()
    {
        if(is_null($this->member)){
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->member->Locale;
    }

    public function getTimeZone()
    {
        if(is_null($this->member)){
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return "";
    }

    public function getDateOfBirth()
    {
        if(is_null($this->member)){
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return "";
    }
}