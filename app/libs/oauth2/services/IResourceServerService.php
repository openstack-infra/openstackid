<?php

namespace oauth2\services;

use oauth2\models\IResourceServer;
/**
 * Interface IResourceServerService
 * @package oauth2\services
 */
interface IResourceServerService {


    /**
     * get a resource server by id
     * @param $id id of resource server
     * @return IResourceServer
     */
    public function get($id);

    /**
     * @param int $page_size
     * @param int $page_nbr
     * @return mixed
     */
    public function getAll($page_nbr = 1,$page_size = 10, array $filters = array(), array $fields=array('*'));

    /**
     * @param IResourceServer $resource_server
     * @return bool
     */
    public function save(IResourceServer $resource_server);

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws \oauth2\exceptions\InvalidResourceServer
     */
    public function update($id, array $params);

    /**
     * sets resource server status (active/deactivated)
     * @param $id id of resource server
     * @param bool $status status (active/non active)
     * @return void
     */
    public function setStatus($id,$status);

    /**
     * deletes a resource server
     * @param $id id of resource server
     * @return bool
     */
    public function delete($id);


    /**
     * Creates a new resource server instance, and a brand new
     * confidential registered client associated with it
     * @param $host
     * @param $ip
     * @param $friendly_name
     * @param bool $active
     * @return IResourceServer
     */
    public function add($host,$ip,$friendly_name, $active);


    /**
     * @param $id resource server id
     * @return string
     */
    public function regenerateClientSecret($id);

    /**
     * @param string $ip
     * @return IResourceServer
     */
    public function getByIPAddress($ip);
} 