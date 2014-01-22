<?php

namespace oauth2\services;

use oauth2\models\IApiEndpoint;

interface IApiEndpointService {

    /**
     * @param $url
     * @param $http_method
     * @return IApiEndpoint
     */
    public function getApiEndpointByUrlAndMethod($url,$http_method);

    /**
     * @param $id
     * @return IApiEndpoint
     */
    public function get($id);

    /**
     * @param int $page_size
     * @param int $page_nbr
     * @return mixed
     */
    public function getAll($page_size=10,$page_nbr=1);


    /**
     * Adds a new api endpoint to an existent api
     * @param string $name
     * @param string $description
     * @param boolean $active
     * @param string $route
     * @param string $http_method
     * @param int $api_id
     * @return IApiEndpoint
     */
    public function add($name, $description, $active, $route, $http_method, $api_id);


    /**
     * Adds a new required scope to a given api endpoint,
     * given scope must belongs to owner api of the given endpoint
     * @param int $api_endpoint_id
     * @param int $scope_id
     * @return boolean
     * @throws \oauth2\exceptions\InvalidApiScope
     * @throws \oauth2\exceptions\InvalidApiEndpoint
     */
    public function addRequiredScope($api_endpoint_id, $scope_id);

    /**
     * Remove a required scope to a given api endpoint,
     * given scope must belongs to owner api of the given endpoint
     * @param int $api_endpoint_id
     * @param int $scope_id
     * @return boolean
     * @throws \oauth2\exceptions\InvalidApiScope
     * @throws \oauth2\exceptions\InvalidApiEndpoint
     */
    public function removeRequiredScope($api_endpoint_id, $scope_id);

    /**
     * deletes a given api endpoint
     * @param int $id
     * @return boolean
     */
    public function delete($id);

    public function save(IApiEndpoint $api_endpoint);

    /**
     * Updates attributes of a endpoint api given instance
     * @param int $id
     * @param array $params
     * @return bool
     * @throws \oauth2\exceptions\InvalidApiEndpoint
     */
    public function update($id, array $params);

    /**
     * @param int $id
     * @param boolean $active
     * @return boolean
     */
    public function setStatus($id, $active);




} 