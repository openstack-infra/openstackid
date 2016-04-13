<?php namespace Utils\Services;
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
use App;

/**
 * Class ServiceLocator
 * @package Utils\Services
 */
final class ServiceLocator
{
    /**
     * @var ServiceLocator
     */
    private static $instance = null;

    private function __construct()
    {
    }

    /**
     * @return null|ServiceLocator
     */
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new ServiceLocator();
        }

        return self::$instance;
    }

    /**
     * @param $service_id
     * @return mixed
     */
    public function getService($service_id)
    {
        $service =  App::make($service_id);
        return $service;
    }

    private function __clone()
    {
    }
}