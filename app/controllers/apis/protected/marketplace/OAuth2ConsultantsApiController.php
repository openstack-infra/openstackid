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
use models\marketplace\repositories\IConsultantRepository;
use oauth2\IResourceServerContext;
use utils\services\ILogService;

/**
 * Class OAuth2ConsultantsApiController
 */
class OAuth2ConsultantsApiController extends OAuth2CompanyServiceApiController {


    /**
     * @param IConsultantRepository  $repository
     * @param IResourceServerContext $resource_server_context
     * @param ILogService            $log_service
     */
    public function __construct (IConsultantRepository $repository, IResourceServerContext  $resource_server_context, ILogService $log_service){
        $this->repository = $repository;
        parent::__construct($resource_server_context,$log_service);
    }

    /**
     * query string params:
     * page: You can specify further pages
     * per_page: custom page size up to 100 ( min 10)
     * status: cloud status ( active , not active, all)
     * order_by: order by field
     * order_dir: order direction
     * @return mixed
     */
    public function getConsultants()
    {
        return $this->getCompanyServices();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getConsultant($id)
    {
      return $this->getCompanyService($id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOffices($id)
    {
        try{
            $consultant = $this->repository->getById($id);

            if(!$consultant)
                return $this->error404();

            $offices = $consultant->offices();
            $res = array();

            foreach($offices as $office){
                array_push($res, $office->toArray());
            }
            return $this->ok(array('offices' => $res));
        }
        catch(Exception $ex){
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }
}