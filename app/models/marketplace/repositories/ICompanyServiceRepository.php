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

namespace models\marketplace\repositories;

use IBaseRepository;

interface ICompanyServiceRepository extends IBaseRepository {

    const Status_All = 'all';
    const Status_active = 'active';
    const Status_non_active = 'non_active';

    const Order_date = 'date';
    const Order_name = 'name';
    /**
     * @param int    $page
     * @param int    $per_page
     * @param string $status
     * @param string $order_by
     * @param string $order_dir
     * @return \IEntity[]
     */
    public function getAll($page = 1, $per_page = 1000, $status = ICompanyServiceRepository::Status_All, $order_by = ICompanyServiceRepository::Order_date, $order_dir = 'asc');
}