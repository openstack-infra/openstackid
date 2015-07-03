<?php

namespace oauth2\services;

use oauth2\models\IUserConsent;

/**
 * Interface IUserConsentService
 * @package oauth2\services
 */
interface IUserConsentService
{
    /**
     * @param $user_id
     * @param $client_id
     * @param $scopes
     * @return IUserConsent
     */
    public function get($user_id, $client_id, $scopes);

    /**
     * @param $user_id
     * @param $client_id
     * @param $scopes
     * @return IUserConsent
     */
    public function add($user_id, $client_id, $scopes);
} 