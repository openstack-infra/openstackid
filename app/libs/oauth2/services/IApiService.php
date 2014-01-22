<?php

namespace oauth2\services;

use oauth2\models\IApi;


interface IApiService {
    /**
     * @param $url
     * @param $http_method
     * @return IApi
     */
    public function getApiByUrlAndMethod($url,$http_method);

    /**
     * @param $api_id
     * @return IApi
     */
    public function get($api_id);

    /**
     * @param $api_name
     * @return IApi
     */
    public function getByName($api_name);

    /**
     * @param $id
     * @return bool
     */
    public function delete($id);

    /**
     * @param $name
     * @param $description
     * @param $active
     * @param $route
     * @param $http_method
     * @param $resource_server_id
     * @return IApi
     */
    public function addApiEndpoint($name, $description, $active,$route, $http_method, $resource_server_id);


    /**
     * @param IApi $endpoint
     * @return void
     */
    public function save(IApi $endpoint);

    /**
     * @param $id
     * @param $active
     * @return bool
     */
    public function setStatus($id,$active);

    /**
     * @param int $page_size
     * @param int $page_nbr
     * @return mixed
     */
    public function getAll($page_size=10,$page_nbr=1);

} 