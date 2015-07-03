<?php

namespace openid\services;

use openid\requests\OpenIdMessageMemento;
/**
 * Interface IMementoOpenIdSerializerService
 * @package openid\services
 */
interface IMementoOpenIdSerializerService {

    /**
     * @param OpenIdMessageMemento $memento
     * @return void
     */
    public function serialize(OpenIdMessageMemento $memento);

    /**
     * @return OpenIdMessageMemento
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