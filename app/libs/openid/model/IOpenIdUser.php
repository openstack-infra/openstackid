<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 3:55 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\model;


interface IOpenIdUser {

    public function getId();
    public function getIdentifier();
    public function getEmail();
    public function getFirstName();
    public function getLastName();
    public function getFullName();
    public function getNickName();
    public function getGender();
    public function getCountry();
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
}