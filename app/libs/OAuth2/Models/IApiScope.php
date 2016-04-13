<?php namespace OAuth2\Models;
/**
 * Interface IApiScope
 * @see http://tools.ietf.org/html/rfc6749#section-3.3
 * @package OAuth2\Models
 */
interface IApiScope extends IScope {

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