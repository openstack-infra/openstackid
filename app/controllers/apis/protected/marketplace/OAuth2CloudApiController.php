<?php
/**
 * Copyright 2015 Openstack Foundation
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

use oauth2\IResourceServerContext;
use utils\services\ILogService;
use models\marketplace\repositories\ICloudServiceRepository;

/**
 * Class OAuth2CloudApiController
 */
abstract class OAuth2CloudApiController extends OAuth2CompanyServiceApiController {


    /**
     * query string params:
     * page: You can specify further pages
     * per_page: custom page size up to 100 ( min 10)
     * status: cloud status ( active , not active, all)
     * order_by: order by field
     * order_dir: order direction
     * @return mixed
     */
    public function getClouds()
    {
       return $this->getCompanyServices();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCloud($id)
    {
        return $this->getCompanyService($id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCloudDataCenters($id)
    {
        try{
            $cloud = $this->repository->getById($id);

            if(!$cloud)
                return $this->error404();

            $data_center_regions = $cloud->datacenters_regions();
            $res = array();

            foreach($data_center_regions as $region){
                $data      = $region->toArray();
                $locations = $region->locations();
                $data_locations = array();
                foreach($locations as $loc){
                    array_push($data_locations, $loc->toArray());
                }
                $data['locations'] = $data_locations;
                array_push($res, $data);
            }

            return $this->ok(array('datacenters' => $res ));
        }
        catch(Exception $ex){
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }
}