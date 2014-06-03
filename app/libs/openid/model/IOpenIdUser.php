<?php

namespace openid\model;

/**
 * Interface IOpenIdUser
 * @package openid\model
 */
interface IOpenIdUser {
    /**
     *
     */
    const OpenstackIdServerAdminGroup = 'openstackid-server-admin';

    /**
     * @return bool
     */
    public function isOpenstackIdAdmin();

    public function getId();
    public function getIdentifier();
    public function getEmail();
    public function getFirstName();
    public function getLastName();
    public function getFullName();
    public function getNickName();
    public function getGender();
    public function getCountry();
    public function getStreetAddress();
    public function getRegion();
    public function getLocality();
    public function getPostalCode();
    public function getLanguage();
    public function getTimeZone();
    public function getDateOfBirth();
    public function getShowProfileFullName();
    public function getShowProfilePic();
    public function getShowProfileBio();
    public function getShowProfileEmail();
    public function getBio();
    public function getPic();
    public function getActions();
	public function getTrustedSites();
}