<?php namespace Services\OpenId;
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
use OpenId\Services\IServerExtensionsService;
use Utils\Services\ServiceLocator;
use Models\OpenId\ServerExtension;
use ReflectionClass;

/**
 * Class ServerExtensionsService
 * @package Services\OpenId
 */
class ServerExtensionsService implements IServerExtensionsService
{

	/**
	 * @return array
	 */
	public function getAllActiveExtensions()
    {
        $extensions = ServerExtension::where('active', '=', true)->get();
        $res = array();
        foreach ($extensions as $extension) {
            $class_name = $extension->extension_class;
            if (empty($class_name)) continue;

	        $class              = new ReflectionClass($class_name);
	        $constructor        = $class->getConstructor();
	        $constructor_params = $constructor->getParameters();

	        $deps = array();

	        foreach($constructor_params as $constructor_param){
				$param_class  = $constructor_param->getClass();
				$name         = $constructor_param->getName();
				if(is_null($param_class)){
					array_push($deps,$extension->$name);
				}
		        else{
			        $service = ServiceLocator::getInstance()->getService($param_class->getName());
			        array_push($deps,$service);
		        }
			}

	        $implementation =  $class->newInstanceArgs($deps);

            array_push($res, $implementation);
        }
        return $res;
    }
}