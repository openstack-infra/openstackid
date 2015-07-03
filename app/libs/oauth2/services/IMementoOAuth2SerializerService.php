<?php

namespace oauth2\services;

use oauth2\requests\OAuth2AuthorizationRequest;
use oauth2\requests\OAuth2RequestMemento;

/**
 * Interface IMementoOAuth2SerializerService
 * @package oauth2\services
 */
interface IMementoOAuth2SerializerService {

    /**
     * @param OAuth2RequestMemento $memento
     * @return void
     */
    public function serialize(OAuth2RequestMemento $memento);

    /**
     * @return OAuth2RequestMemento
     */
    public function load();

    /**
     * @return void
     */
    public function forget();

    /**
     * @return bool
     */
    public function exists();
} 