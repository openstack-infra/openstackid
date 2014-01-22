<?php

namespace oauth2\services;

use oauth2\models\IApi;


interface IApiService {

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
     * @param $resource_server_id
     * @return IApi
     */
    public function add($name, $description, $active, $resource_server_id);


    /**
     * @param $id
     * @param array $params
     * @throws \oauth2\exceptions\InvalidApi
     */
    public function update($id, array $params);
    /**
     * @param IApi $api
     * @return void
     */
    public function save(IApi $api);

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