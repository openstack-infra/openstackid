<?php

namespace services\openid;

use openid\services\IServerExtensionsService;
use utils\services\ServiceLocator;
use ServerExtension;
use ReflectionClass;

/**
 * Class ServerExtensionsService
 * @package services\openid
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