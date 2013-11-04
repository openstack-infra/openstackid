<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace services;

use openid\services\IServerExtensionsService;

class ServerExtensionsService implements IServerExtensionsService{

    public function getAllActiveExtensions()
    {
        $extensions = \ServerExtension::where('active', '=', true)->get();
        $res = array();
        foreach($extensions as $extension){
            $class = $extension->extension_class;
            if(empty($class) /*|| !class_exists($class)*/) continue;
            $implementation = new $class($extension->name, $extension->namespace,$extension->view_name, $extension->description);
            array_push($res,$implementation);
        }
        return $res;
    }
}