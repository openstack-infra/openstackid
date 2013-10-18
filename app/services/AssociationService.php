<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/18/13
 * Time: 12:28 PM
 * To change this template use File | Settings | File Templates.
 */

namespace services;
use openid\model\IAssociation;
use openid\services\IAssociationService;
use \OpenIdAssociation;

class AssociationService implements  IAssociationService{

    /**
     * @param $handle
     * @return IAssociation
     */
    public function getAssociation($handle)
    {
        //todo: need to add expiration logic
        return OpenIdAssociation::where('identifier','=',$handle)->first();
    }

    /**
     * @param IAssociation $association
     * @return bool
     */
    public function addAssociation($handle, $secret,$mac_function, $lifetime, $issued,$type)
    {
        $assoc = new OpenIdAssociation();
        $assoc->identifier = $handle;
        $assoc->secret = $secret;
        $assoc->type = $type;
        $assoc->mac_function = $mac_function;
        $assoc->lifetime = $lifetime;
        $assoc->issued = $issued;
        $assoc->Save();
    }

    /**
     * @param $handle
     * @return bool
     */
    public function deleteAssociation($handle)
    {
        $assoc = OpenIdAssociation::where('identifier','=',$handle)->first();
        $assoc->delete();
    }
}