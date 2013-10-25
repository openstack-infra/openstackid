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
use \DateTime;
use \DateInterval;
class AssociationService implements  IAssociationService{

    /**
     * @param $handle
     * @return IAssociation
     */
    public function getAssociation($handle)
    {
        $assoc =  OpenIdAssociation::where('identifier','=',$handle)->first();
        if(!is_null($assoc)){
            $issued_date = new DateTime($assoc->issued);
            $life_time   = $assoc->lifetime;
            $issued_date->add(new DateInterval('PT'.$life_time.'S'));
            $now         = new DateTime(gmdate("Y-m-d H:i:s", time()));
            if($now>$issued_date){
                $this->deleteAssociation($handle);
                $assoc = null;
            }
        }
        return $assoc;
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
        if(!is_null($assoc)){
            $assoc->delete();
            return true;
        }
        return false;
    }

    /**
     * For verifying signatures an OP MUST only use private associations and MUST NOT
     * use associations that have shared keys. If the verification request contains a handle
     * for a shared association, it means the Relying Party no longer knows the shared secret,
     * or an entity other than the RP (e.g. an attacker) has established this association with the OP.
     * @param $handle
     * @return mixed
     */
    public function getAssociationType($handle)
    {
        $assoc = OpenIdAssociation::where('identifier','=',$handle)->first();
        if(!is_null($assoc)){
            return $assoc->type;
        }
        return false;
    }
}