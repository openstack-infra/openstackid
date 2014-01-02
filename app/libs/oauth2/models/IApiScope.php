<?php

namespace oauth2\models;

/**
 * Interface IApiScope
 * http://tools.ietf.org/html/rfc6749#section-3.3
 * @package oauth2\models
 */
interface IApiScope {
    public function getShortDescription();
    public function getName();
    public function getDescription();
    public function isActive();
    public function getApiName();
} 