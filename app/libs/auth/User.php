<?php

namespace auth;

use Illuminate\Auth\UserInterface;
use Member;
use MemberPhoto;
use openid\model\IOpenIdUser;
use openid\services\OpenIdServiceCatalog;
use utils\services\Registry;
use oauth2\models\IOAuth2User;
use Eloquent;

class User extends Eloquent implements UserInterface, IOpenIdUser, IOAuth2User
{

    protected $table = 'openid_users';

    private $member;

    public function trusted_sites()
    {
        return $this->hasMany("OpenIdTrustedSite", 'user_id');
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

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        if (is_null($this->member)) {
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
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->member->Password;
    }

    public function getIdentifier()
    {
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->identifier;
    }

    public function getEmail()
    {
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->external_id;
    }

    public function getFullName()
    {
        return $this->getFirstName() . " " . $this->getLastName();
    }

    public function getFirstName()
    {
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->member->FirstName;
    }

    public function getLastName()
    {
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->member->Surname;
    }

    public function getNickName()
    {
        return $this->getFullName;
    }

    public function getGender()
    {
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return "";
    }

    public function getCountry()
    {
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->member->Country;
    }

    public function getLanguage()
    {
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->member->Locale;
    }

    public function getTimeZone()
    {
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return "";
    }

    public function getDateOfBirth()
    {
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return "";
    }

    public function getId()
    {
        return $this->id;
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
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        return $this->member->Bio;
    }

    public function getPic()
    {
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        $url     = asset('img/generic-profile-photo.png');

        $photoId = $this->member->PhotoID;

        if (!is_null($photoId) && is_numeric($photoId) && $photoId > 0) {
            $photo                            = MemberPhoto::where('ID', '=', $photoId)->first();
            if(!is_null($photo)){
                $server_configuration_service = Registry::getInstance()->get(OpenIdServiceCatalog::ServerConfigurationService);
                $url                          = $server_configuration_service->getConfigValue("Assets.Url").$photo->Filename;
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
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        $group = $this->member->groups()->get('code','=','oauth2-system-scope-admin')->first();
        return !is_null($group);
    }

    /**
     * Is Server Administrator
     * @return bool
     */
    public function IsServerAdmin()
    {
        if (is_null($this->member)) {
            $this->member = Member::where('Email', '=', $this->external_id)->first();
        }
        $group = $this->member->groups()->get('code','=','oauth2-server-admin')->first();
        return !is_null($group);
    }
}