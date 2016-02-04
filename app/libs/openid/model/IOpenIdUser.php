<?php

namespace openid\model;

/**
 * Interface IOpenIdUser
 * @package openid\model
 */
interface IOpenIdUser
{
    /**
     *
     */
    const OpenstackIdServerAdminGroup = 'openstackid-server-admin';

    /**
     * @return bool
     */
    public function isOpenstackIdAdmin();

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getFirstName();

    /**
     * @return string
     */
    public function getLastName();

    /**
     * @return string
     */
    public function getFullName();

    /**
     * @return string
     */
    public function getNickName();

    /**
     * @return string
     */
    public function getGender();

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @return string
     */
    public function getStreetAddress();

    /**
     * @return string
     */
    public function getRegion();

    /**
     * @return string
     */
    public function getLocality();

    /**
     * @return string
     */
    public function getPostalCode();

    /**
     * @return string
     */
    public function getFormattedAddress();

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

    /**
     * @return int
     */
    public function getExternalIdentifier();

    /**
     * @return bool
     */
    public function isEmailVerified();
}