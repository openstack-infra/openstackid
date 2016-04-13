<?php namespace OpenId\Services;
/**
 * Copyright 2016 OpenStack Foundation
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
use Exception;
use OpenId\Models\IOpenIdUser;
use OpenId\Models\ITrustedSite;
/**
 * Interface ITrustedSitesService
 * @package OpenId\Services
 */
interface ITrustedSitesService
{
    /**
     * @param IOpenIdUser $user
     * @param string $realm
     * @param $policy
     * @param array $data
     * @return bool|ITrustedSite
     * @throws Exception
     */
    public function addTrustedSite(IOpenIdUser $user, $realm, $policy, $data = array());

    /**
     * @param $id
     * @return bool
     */
    public function delTrustedSite($id);

    /**
     * @param IOpenIdUser $user
     * @param $realm
     * @param array $data
     * @return mixed
     */
    public function getTrustedSites(IOpenIdUser $user, $realm, $data = array());

}