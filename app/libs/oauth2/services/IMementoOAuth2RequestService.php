<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 12/2/13
 * Time: 5:05 PM
 */

namespace oauth2\services;


interface IMementoOAuth2RequestService {
    /**
     * Save current OAuth2Request till next request
     * @return bool
     */
    public function saveCurrentRequest();

    /** Retrieve last OpenIdMessage
     * @return OAuth2Request;
     */
    public function getCurrentRequest();

    public function clearCurrentRequest();
} 