<?php

namespace services\openid;
use openid\exceptions\OpenIdInvalidRealmException;
use openid\exceptions\ReplayAttackException;
use openid\exceptions\InvalidAssociation;
use openid\helpers\OpenIdErrorMessages;
use openid\model\IAssociation;
use openid\services\IAssociationService;
use OpenIdAssociation;
use utils\exceptions\UnacquiredLockException;
use utils\services\ILockManagerService;
use utils\services\ICacheService;
use openid\repositories\IOpenIdAssociationRepository;

/**
 * Class AssociationService
 * @package services
 */
class AssociationService implements IAssociationService
{

    private $lock_manager_service;
    private $cache_service;
	private $repository;

	/**
	 * @param IOpenIdAssociationRepository $repository
	 * @param ILockManagerService          $lock_manager_service
	 * @param ICacheService                $cache_service
	 */
	public function __construct(IOpenIdAssociationRepository $repository,
								ILockManagerService $lock_manager_service,
	                            ICacheService $cache_service)
    {
        $this->lock_manager_service = $lock_manager_service;
        $this->cache_service        = $cache_service;
	    $this->repository           = $repository;
    }

    /**
     * gets a given association by handle, and if association exists and its type is private, then lock it
     * to prevent subsequent usage ( private association could be used once)
     * @param $handle
     * @param null $realm
     * @return null|IAssociation|OpenIdAssociation
     * @throws \openid\exceptions\ReplayAttackException
     * @throws \openid\exceptions\InvalidAssociation
     * @throws \openid\exceptions\OpenIdInvalidRealmException
     */
    public function getAssociation($handle, $realm = null)
    {

        $lock_name = 'lock.get.assoc.' . $handle;

        try {
            // check if association is on cache
            if (!$this->cache_service->exists($handle)) {
                // if not , check on db
                $assoc = $this->repository->getByHandle($handle);
                if(is_null($assoc))
                    throw new InvalidAssociation(sprintf('openid association %s does not exists!',$handle));
                //check association lifetime ...
                $remaining_lifetime = $assoc->getRemainingLifetime();
                if ($remaining_lifetime < 0) {
                    $this->deleteAssociation($handle);
                    return null;
                }
				//convert secret to hexa representation
	            // bin2hex
	            $secret_unpack  = \unpack('H*', $assoc->secret);
	            $secret_unpack  = array_shift($secret_unpack);
                //repopulate cache
                $this->cache_service->storeHash($handle, array(
                    "type"         => $assoc->type,
                    "mac_function" => $assoc->mac_function,
                    "issued"       => $assoc->issued,
                    "lifetime"     => $assoc->lifetime,
		            "secret"       => $secret_unpack,
                    "realm"        => $assoc->realm),
                    $remaining_lifetime);
            }

            //get hash from cache
            $cache_values = $this->cache_service->getHash($handle, array(
                "type",
                "mac_function",
                "issued",
                "lifetime",
                "secret",
                "realm"));

            if ($cache_values['type'] == IAssociation::TypePrivate) {
                if (is_null($realm) || empty($realm) || $cache_values['realm'] != $realm) {
                    throw new OpenIdInvalidRealmException(sprintf(OpenIdErrorMessages::InvalidPrivateAssociationMessage, $handle, $realm));
                }
                // only one time we could use this handle
                $this->lock_manager_service->acquireLock($lock_name);
            }

	        //convert hex 2 bin
	        $secret = \pack('H*', $cache_values['secret']);
            $assoc  = new OpenIdAssociation();

            $assoc->type         = $cache_values['type'];
            $assoc->mac_function = $cache_values['mac_function'];
            $assoc->issued       = $cache_values['issued'];
            $assoc->lifetime     = intval($cache_values['lifetime']);
	        $assoc->secret       = $secret;
            $realm               = $cache_values['realm'];
            if (!empty($realm))
                $assoc->realm = $realm;
            return $assoc;

        } catch (UnacquiredLockException $ex1) {
            throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackPrivateAssociationAlreadyUsed, $handle));
        }
    }

    /**
     * @param $handle
     * @return bool
     */
    public function deleteAssociation($handle)
    {
        $this->cache_service->delete($handle);
        $assoc = $this->repository->getByHandle($handle);
	    if (!is_null($assoc)) {
            return $this->repository->delete($assoc);
        }
        return false;
    }

	/**
	 * @param IAssociation $association
	 * @return IAssociation|OpenIdAssociation
	 * @throws \openid\exceptions\ReplayAttackException
	 */
	public function addAssociation(IAssociation $association)
    {
        $assoc = new OpenIdAssociation();
        try {
            $lock_name = 'lock.add.assoc.' . $association->getHandle();
            $this->lock_manager_service->acquireLock($lock_name);

            $assoc->identifier   = $association->getHandle();;
            $assoc->secret       = $association->getSecret();
            $assoc->type         = $association->getType();;
            $assoc->mac_function = $association->getMacFunction();
            $assoc->lifetime     = intval($association->getLifetime());
            $assoc->issued       = $association->getIssued();

            if (!is_null($association->getRealm()))
                $assoc->realm = $association->getRealm();

            if ($association->getType() == IAssociation::TypeSession) {
                $this->repository->add($assoc);
            }
	        //convert secret to hexa representation
	        // bin2hex
	        $secret_unpack = \unpack('H*', $association->getSecret());
	        $secret_unpack = array_shift($secret_unpack);

            $this->cache_service->storeHash($association->getHandle(),
	            array(
	                "type"         => $association->getType(),
	                "mac_function" => $association->getMacFunction(),
	                "issued"       => $association->getIssued(),
	                "lifetime"     => intval($association->getLifetime()),
		            "secret"       => $secret_unpack,
		            "realm"        => !is_null($association->getRealm())?$association->getRealm():''
	            ),
	            intval($association->getLifetime())
            );

        } catch (UnacquiredLockException $ex1) {
            throw new ReplayAttackException(sprintf(OpenIdErrorMessages::ReplayAttackPrivateAssociationAlreadyUsed, $association->getHandle()));
        }
        return $assoc;
    }

}