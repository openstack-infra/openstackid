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
interface IApi {
    /**
     * @return IResourceServer
     */
    public function getResourceServer();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLogo();

    /**
     * @return string
     */
    public function getDescription();

    public function getScope();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @param string $name
     * @return $this`
     */
    public function setName($name);

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @param bool $active
     * @return $this
     */
    public function setStatus($active);

} 