<?php

namespace auth;

use Eloquent;
use Illuminate\Auth\UserInterface;
use Member;
use MemberPhoto;
use oauth2\models\IApiScope;
use oauth2\models\IApiScopeGroup;
use oauth2\models\IOAuth2User;
use openid\model\IOpenIdUser;
use utils\model\BaseModelEloquent;

/**
 * Class User
 * @package auth
 */
class User extends BaseModelEloquent implements UserInterface, IOpenIdUser, IOAuth2User
{
    protected $table = 'openid_users';

    private $member;

    public function trusted_sites()
    {
        return $this->hasMany("OpenIdTrustedSite", 'user_id');
    }

    public function access_tokens()
    {
        return $this->hasMany('AccessToken', 'user_id');
    }

    public function refresh_tokens()
    {
        return $this->hasMany('RefreshToken', 'user_id');
    }

    public function consents()
    {
        return $this->hasMany('UserConsent', 'user_id');
    }

    public function clients()
    {
        return $this->hasMany("Client", 'user_id');
    }

    public function getActions()
    {
        return $this->actions()->orderBy('created_at', 'desc')->take(10)->get();
    }

    public function actions()
    {
        return $this->hasMany("UserAction", 'user_id');
    }

    public function setMember($member)
    {
        $this->member = $member;
    }

    private function getAssociatedMember()
    {
        if (is_null($this->member)) {
            $this->member = Member::where('ID', '=', $this->external_identifier)->first();
        }

        return $this->member;
    }

    /**
     * Get the unique identifier for the user.
     * the one that is saved as session id on vendor/laravel/framework/src/Illuminate/Auth/Guard.php
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->external_identifier;
    }

    /**
     * Get the password for the user.
     * @return string
     */
    public function getAuthPassword()
    {
        $this->getAssociatedMember();

        return $this->member->Password;
    }

    public function getIdentifier()
    {
        $this->getAssociatedMember();

        return $this->identifier;
    }

    public function getEmail()
    {
        $this->getAssociatedMember();
        if(is_null($this->member)) return false;
        return $this->member->Email;
    }

    /**
     * @return string
     */
    public function getEmailAttribute()
    {
        return  $this->getEmail();
    }

    public function getFullName()
    {
        return $this->getFirstName() . " " . $this->getLastName();
    }

    public function getFirstName()
    {
        $this->getAssociatedMember();

        return $this->member->FirstName;
    }

    public function getLastName()
    {
        $this->getAssociatedMember();

        return $this->member->Surname;
    }

    public function getNickName()
    {
        return $this->getFullName();
    }

    public function getGender()
    {
        $this->getAssociatedMember();

        return $this->member->Gender;
    }

    public function getCountry()
    {
        $this->getAssociatedMember();

        return $this->member->Country;
    }

    public function getLanguage()
    {
        $this->getAssociatedMember();

        return $this->member->Locale;
    }

    public function getTimeZone()
    {
        $this->getAssociatedMember();

        return "";
    }

    public function getDateOfBirth()
    {
        $this->getAssociatedMember();

        return "";
    }

    public function getId()
    {
        return (int)$this->id;
    }

    public function getShowProfileFullName()
    {
        return $this->public_profile_show_fullname;
    }

    public function getShowProfilePic()
    {
        return $this->public_profile_show_photo;
    }

    public function getShowProfileBio()
    {
        return false;
    }

    public function getShowProfileEmail()
    {
        return $this->public_profile_show_email;
    }

    public function getBio()
    {
        $this->getAssociatedMember();

        return $this->member->Bio;
    }

    public function getPic()
    {
        $this->getAssociatedMember();
        $url = asset('img/generic-profile-photo.png');
        $photoId = $this->member->PhotoID;
        if (!is_null($photoId) && is_numeric($photoId) && $photoId > 0) {
            $photo = MemberPhoto::where('ID', '=', $photoId)->first();
            if (!is_null($photo)) {
                $url = $photo->Filename;
            }
        }

        return $url;
    }

    public function getClients()
    {
        return $this->clients()->get();
    }

    /**
     * Could use system scopes on registered clients
     * @return bool
     */
    public function canUseSystemScopes()
    {
        $this->getAssociatedMember();
        $group = $this->member->groups()->where('code', '=', IOAuth2User::OAuth2SystemScopeAdminGroup)->first();

        return !is_null($group);
    }

    /**
     * Is Server Administrator
     * @return bool
     */
    public function isOAuth2ServerAdmin()
    {
        $this->getAssociatedMember();
        $group = $this->member->groups()->where('code', '=', IOAuth2User::OAuth2ServerAdminGroup)->first();

        return !is_null($group);
    }

    /**
     * @return bool
     */
    public function isOpenstackIdAdmin()
    {
        $this->getAssociatedMember();
        $group = $this->member->groups()->where('code', '=', IOpenIdUser::OpenstackIdServerAdminGroup)->first();

        return !is_null($group);
    }

    public function getStreetAddress()
    {
        $this->getAssociatedMember();

        $street_address = $this->member->Address;
        $suburb = $this->member->Suburb;
        if(!empty($suburb))
            $street_address .= ', '.$suburb;
        return $street_address;
    }

    public function getRegion()
    {
        $this->getAssociatedMember();

        return $this->member->State;
    }

    public function getLocality()
    {
        $this->getAssociatedMember();

        return $this->member->City;
    }

    public function getPostalCode()
    {
        $this->getAssociatedMember();

        return $this->member->Postcode;
    }

    public function getTrustedSites()
    {
        return $this->trusted_sites()->get();
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * @return int
     */
    public function getExternalIdentifier()
    {
        return $this->getAuthIdentifier();
    }

    /**
     * @return string
     */
    public function getFormattedAddress()
    {
        $street   = $this->getStreetAddress();
        $region   = $this->getRegion();
        $city     = $this->getLocality();
        $zip_code = $this->getPostalCode();
        $country  = $this->getCountry();

        $complete = $street;

        if(!empty($city))
            $complete .= ', '.$city;

        if(!empty($region))
            $complete .= ', '.$region;

        if(!empty($zip_code))
            $complete .= ', '.$zip_code;

        if(!empty($country))
            $complete .= ', '.$country;

        return $complete;
    }

    /**
     * @return IApiScopeGroup[]
     */
    public function getGroups()
    {
        return $this->groups()->where('active','=',true)->get();
    }

    /**
     * @return mixed
     */
    public function groups()
    {
        return $this->belongsToMany('ApiScopeGroup','oauth2_api_scope_group_users','user_id', 'group_id');
    }

    /**
     * @return IApiScope[]
     */
    public function getGroupScopes()
    {
        $scopes = array();
        $map    = array();
        foreach($this->groups()->where('active','=',true)->get() as $group){
            foreach($group->scopes()->get() as $scope)
            {
                if(!isset($map[$scope->id]))
                    array_push($scopes, $scope);
            }
        }
        return $scopes;
    }
}