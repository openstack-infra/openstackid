<?php

namespace oauth2\models;

/**
 * Interface IApiScope
 * http://tools.ietf.org/html/rfc6749#section-3.3
 * @package oauth2\models
 */
interface IApiScope extends IScope{

    /**
     * @return string
     */
    public function getApiName();

    /**
     * @return string
     */
    public function getApiDescription();

    /**
     * @return string
     */
    public function getApiLogo();

    /**
     * @return IApi
     */
    public function getApi();
}