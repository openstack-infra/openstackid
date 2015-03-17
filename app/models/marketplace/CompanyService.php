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

namespace models\marketplace;
use utils\model\BaseModelEloquent;
/**
 * Class CompanyService
 * @package model\marketplace
 */
class CompanyService extends BaseModelEloquent implements \IEntity{

    protected $hidden = array('ClassName', 'MarketPlaceTypeID', 'EditedByID');

    protected $table = 'CompanyService';

    protected $connection = 'os_members';

    protected $stiClassField = 'ClassName';

    protected $stiBaseClass = 'models\marketplace\CompanyService';

    /**
     * @return int
     */
    public function getIdentifier()
    {
        return (int)$this->ID;
    }
}