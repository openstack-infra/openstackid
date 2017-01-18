<?php namespace OpenId\Models;
/**
 * Interface IOpenIdUser
 * @package OpenId\Models
 */
interface IOpenIdUser
{
    const OpenStackIdServerAdminGroup = 'openstackid-server-admin';

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

    /**
     * @return bool
     */
    public function getShowProfileFullName();

    /**
     * @return bool
     */
    public function getShowProfilePic();

    /**
     * @return bool
     */
    public function getShowProfileBio();

    /**
     * @return bool
     */
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