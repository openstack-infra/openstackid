<?php namespace OAuth2\Models;
/**
 * Interface IPrincipal
 * @package OAuth2\Models
 */
interface IPrincipal
{
    /**
     * @return int
     */
    public function getAuthTime();

    /**
     * @return int
     */
    public function getUserId();

    /**
     * OP Browser state
     * @return string
     */
    public function getOPBrowserState();
}