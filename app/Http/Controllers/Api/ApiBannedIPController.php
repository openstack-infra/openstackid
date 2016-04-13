<?php namespace App\Http\Controllers\Api;
/**
 * Copyright 2015 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use Utils\Services\IBannedIPService;
use Utils\Services\ILogService;
use App\Http\Controllers\ICRUDController;
use Illuminate\Support\Facades\Input;
use Exception;

/**
 * Class ApiBannedIPController
 * @package App\Http\Controllers\Api
 */
class ApiBannedIPController extends AbstractRESTController implements ICRUDController
{

    private $banned_ip_service;

    /**
     * @param IBannedIPService $banned_ip_service
     * @param ILogService $log_service
     */
    public function __construct(IBannedIPService $banned_ip_service, ILogService $log_service)
    {

        parent::__construct($log_service);

        $this->banned_ip_service         = $banned_ip_service;
        $this->allowed_filter_fields     = array();
        $this->allowed_projection_fields = array('*');
    }

    public function get($id)
    {
        try {

            $ip = Input::get("ip", null);
            if (!is_null($ip)) {
                $banned_ip = $this->banned_ip_service->getByIP($ip);
            } else {
                $banned_ip = $this->banned_ip_service->get($id);
            }
            if (is_null($banned_ip)) {
                return $this->error404(array('error' => 'banned ip not found'));
            }

            $data = $banned_ip->toArray();
            return $this->ok($data);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function getByPage()
    {
        try {
            //check for optional filters param on querystring
            $fields    = $this->getProjection(Input::get('fields', null));
            $filters   = $this->getFilters(Input::except('fields', 'limit', 'offset'));
            $page_nbr  = intval(Input::get('offset', 1));
            $page_size = intval(Input::get('limit', 10));

            $list = $this->banned_ip_service->getByPage($page_nbr, $page_size, $filters, $fields);
            $items = array();
            foreach ($list->getItems() as $ip) {
                array_push($items, $ip->toArray());
            }
            return $this->ok(array(
                'page'        => $items,
                'total_items' => $list->getTotal()
            ));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function delete($id = null)
    {
        try {
            if (is_null($id)) {
                $ip = Input::get("ip", null);
            } else {
                $banned_ip = $this->banned_ip_service->get($id);
                $ip        = $banned_ip->ip;
            }
            if (is_null($ip))
                return $this->error400('invalid request');
            $res = $this->banned_ip_service->delete($ip);
            return $res ? $this->deleted() : $this->error404(array('error' => 'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function update()
    {
        // TODO: Implement update() method.
    }
}