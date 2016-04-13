<?php namespace OpenId\Services;
/**
 * Interface IServerExtensionsService
 * @package OpenId\Services
 */
interface IServerExtensionsService {
    /**
     * @return mixed
     */
    public function getAllActiveExtensions();
}