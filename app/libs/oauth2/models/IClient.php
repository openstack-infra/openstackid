<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/3/13
 * Time: 4:37 PM
 */

namespace oauth2\models;


interface IClient {
    const ClientType_Public       = "public";
    const ClientType_Confidential = "confidential";

    public function getClientId();
    public function getClientSecret();
    public function getClientType();
    public function getClientAuthorizedRealms();
    public function getClientScopes();
    public function getClientRegisteredUris();
    public function isScopeAllowed($scope);
    public function isRealmAllowed($realm);
    public function isUriAllowed($uri);
} 