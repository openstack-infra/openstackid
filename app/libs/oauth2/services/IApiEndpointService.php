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

} 