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

namespace repositories\marketplace;

use models\marketplace\PublicCloudService;
use utils\services\ILogService;
use models\marketplace\repositories\ICloudServiceRepository;
use models\marketplace\repositories\IPublicCloudServiceRepository;
use models\marketplace\ICloudService;
use repositories\marketplace\EloquentCloudServiceRepository;
use DB;

/**
 * Class EloquentPublicCloudServiceRepository
 * @package repositories\marketplace
 */
class EloquentPublicCloudServiceRepository extends EloquentCompanyServiceRepository
implements IPublicCloudServiceRepository {

    /**
     * @param PublicCloudService  $public_cloud
     * @param ILogService $log_service
     */
    public function __construct(PublicCloudService $public_cloud, ILogService $log_service){
        $this->entity       = $public_cloud;
        $this->log_service  = $log_service;
    }

}