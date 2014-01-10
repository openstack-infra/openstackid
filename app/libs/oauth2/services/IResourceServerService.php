<?php

namespace oauth2\services;

use oauth2\models\IResourceServer;
/**
 * Interface IResourceServerService
 * @package oauth2\services
 */
interface IResourceServerService {

    /**
     * @param int $page_size
     * @param int $page_nbr
     * @return mixed
     */
    public function getAll($page_size=10,$page_nbr=1);

    /**
     * @param IResourceServer $resource_server
     * @return void
     */
    public function save(IResourceServer $resource_server);

    /**
     * sets resource server status (active/deactivated)
     * @param $resource_server_id id of resource server
     * @param bool $status status (active/non active)
     * @return void
     */
    public function setStatus($resource_server_id,$status);

    /**
     * deletes a resource server
     * @param $resource_server_id id of resource server
     * @return bool
     */
    public function delete($resource_server_id);


    /**
     * get a resource server by id
     * @param $resource_server_id id of resource server
     * @return IResourceServer
     */
    public function get($resource_server_id);

    /** Creates a new resource server instance
     * @param $host
     * @param $ip
     * @param $friendly_name
     * @param bool $active
     * @return IResourceServer
     */
    public function addResourceServer($host,$ip,$friendly_name, $active);


    /**
     * @param $resource_server_id
     * @return string
     */
    public function regenerateResourceServerClientSecret($resource_server_id);
} 