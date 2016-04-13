<?php namespace Auth;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use Models\Member;
use OAuth2\Models\IApiScope;
use OAuth2\Models\IApiScopeGroup;
use OAuth2\Models\IOAuth2User;
use OpenId\Models\IOpenIdUser;
use Utils\Exceptions\EntityNotFoundException;
use Utils\Model\BaseModelEloquent;
use Utils\Model\IEntity;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
/**
 * Class User
 * @package Auth
 */
class User extends BaseModelEloquent implements AuthenticatableContract, IOpenIdUser, IOAuth2User, IEntity
{
    use Authenticatable;

    protected $table = 'openid_users';

    /**
     * @var Member
     */
    private $member;

    public function trusted_sites()
    {
        return $this->hasMany("Models\OpenId\OpenIdTrustedSite", 'user_id');
    }

    public function access_tokens()
    {
        return $this->hasMany('Models\OAuth2\AccessToken', 'user_id');
    }

    public function refresh_tokens()
    {
        return $this->hasMany('Models\OAuth2\RefreshToken', 'user_id');
    }

    public function consents()
    {
        return $this->hasMany('Models\OAuth2\UserConsent', 'user_id');
    }

    public function clients()
    {
        return $this->hasMany("Models\OAuth2\Client", 'user_id');
    }

    public function getActions()
    {
        return $this->actions()->orderBy('created_at', 'desc')->take(10)->get();
    }

    public function actions()
    {
        return $this->hasMany("Models\UserAction", 'user_id');
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
        if (is_null($this->member)) throw new EntityNotFoundException(sprintf('member id %s',$this->external_identifier));
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
        return 'profile_images/members/'.$this->member->ID;
    }

    public function getClients()
    {
        $own_clients = $this->clients()->get();
        $managed_clients = $this->managed_clients()->get();
        return $own_clients->merge($managed_clients);
    }

    public function managed_clients()
    {
        return $this->belongsToMany('Models\OAuth2\Client', 'oauth2_client_admin_users', 'user_id', 'oauth2_client_id');
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
        $group = $this->member->groups()->where('code', '=', IOpenIdUser::OpenStackIdServerAdminGroup)->first();

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
        return $this->belongsToMany('Models\OAuth2\ApiScopeGroup','oauth2_api_scope_group_users','user_id', 'group_id');
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

    /**
     * @return bool
     */
    public function isEmailVerified()
    {
        $this->getAssociatedMember();

        return $this->member->isEmailVerified();
    }
}