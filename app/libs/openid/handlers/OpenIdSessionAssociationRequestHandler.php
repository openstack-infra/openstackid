<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:43 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\handlers;


use openid\OpenIdMessage;

class OpenIdSessionAssociationRequestHandler extends OpenIdMessageHandler{

    protected function InternalHandle(OpenIdMessage $message){

    }

    protected function CanHandle(OpenIdMessage $message)
    {
        $res = false;
        return $res;
    }
}