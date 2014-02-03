<?php

namespace oauth2\models;
/**
 * Interface IUserConsent
 * @package oauth2\models
 */
interface IUserConsent {
    public function getScope();
    public function getClient();
    public function getUser();
} 