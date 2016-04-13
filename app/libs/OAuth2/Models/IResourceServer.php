<?php namespace OAuth2\Models;
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
/**
 * Interface IResourceServer
 * @package OAuth2\Models
 */
interface IResourceServer {

    /**
     * get resource server host
     * @return string
     */
    public function getHost();

    /**
     * @param string $host
     * @return $this
     */
    public function setHost($host);

    /**
     * tells if resource server is active or not
     * @return bool
     */
    public function isActive();

    /**
     * @param bool $active
     * @return $this
     */
    public function setActive($active);


    /**
     * get resource server friendly name
     * @return string
     */
    public function getFriendlyName();

    /**
     * @param string $friendly_name
     * @return $this
     */
    public function setFriendlyName($friendly_name);

    /**
     * @return IClient
     */
    public function getClient();

    /**
     * @param string $ip
     * @return bool
     */
    public function isOwn($ip);

    /**
     * @return string
     */
    public function getIPAddresses();
} 