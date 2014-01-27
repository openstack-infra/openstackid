<?php

namespace oauth2\models;

/**
 * Interface IOAuth2User
 * @package oauth2\models
 */
interface IOAuth2User {

    public function getClients();

    /**
     * Could use system scopes on registered clients
     * @return bool
     */
    public function canUseSystemScopes();


    /**
     * Is Server Administrator
     * @return bool
     */
    public function IsServerAdmin();
} 