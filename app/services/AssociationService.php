<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/18/13
 * Time: 12:28 PM
 * To change this template use File | Settings | File Templates.
 */

namespace services;
use openid\exceptions\ReplayAttackException;
use openid\model\IAssociation;
use openid\services\IAssociationService;
use \OpenIdAssociation;
use \DateTime;
use \DateInterval;
use openid\exceptions\OpenIdInvalidRealmException;

class AssociationService implements  IAssociationService{

    private $redis;

    public function __construct(){
        $this->redis = \RedisLV4::connection();
    }

    /**
     * @param $handle
     * @param null $realm
     * @return null|IAssociation
     * @throws \openid\exceptions\ReplayAttackException
     * @throws \openid\exceptions\OpenIdInvalidRealmException
     */
    public function getAssociation($handle, $realm=null)
    {
        $assoc =  OpenIdAssociation::where('identifier','=',$handle)->first();
        if(!is_null($assoc)){
            $issued_date = new DateTime($assoc->issued);
            if($assoc->type == IAssociation::TypePrivate && !is_null($realm) && !empty($realm)){
                if($assoc->realm!=$realm){
                    throw new OpenIdInvalidRealmException(sprintf("Private Association %s was not emit for requested realm %s",$handle,$realm));
                }
                $cur_time      = time();
                $lock_lifetime = 180;
                $success       = $this->redis->setnx('lock.'.$handle,$cur_time+$lock_lifetime+1);
                if(!$success){
                    throw new ReplayAttackException(sprintf("Private Association %s already used",$handle));
                }
            }
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
    public function addAssociation($handle, $secret,$mac_function, $lifetime, $issued,$type,$realm=null)
    {
        $assoc = new OpenIdAssociation();
        $assoc->identifier   = $handle;
        $assoc->secret       = $secret;
        $assoc->type         = $type;
        $assoc->mac_function = $mac_function;
        $assoc->lifetime     = $lifetime;
        $assoc->issued       = $issued;
        if(!is_null($realm))
            $assoc->realm        = $realm;
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