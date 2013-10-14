<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:05 PM
 * To change this template use File | Settings | File Templates.
 */

namespace repositories;

use openid\repositories\all;
use openid\repositories\IServerExtensionsRepository;

class ServerExtensionsRepositoryEloquent implements  IServerExtensionsRepository{

    /**
     * @return all active server extensions
     */
    public function  GetAllExtensions()
    {
        $extensions =  array();

        $ext1 = new \ServerExtension();
        $ext1->name='AX';
        $ext1->description='OpenID service extension for exchanging identity information between endpoints';
        $ext1->namespace='http://openid.net/srv/ax/1.0';
        $ext1->active = true;
        $ext1->extension_class='';
        array_push($extensions,$ext1) ;

        $ext2 = new \ServerExtension();
        $ext2->name='PAPE';
        $ext2->description='OpenID service extension for exchanging identity information between endpoints';
        $ext2->namespace='http://specs.openid.net/extensions/pape/1.0';
        $ext2->active = true;
        $ext2->extension_class='';
        array_push($extensions,$ext2) ;

        return $extensions;
    }
}