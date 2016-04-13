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
use OpenId\Exceptions\InvalidAssociation;
use OpenId\Exceptions\OpenIdInvalidRealmException;
use OpenId\Exceptions\ReplayAttackException;
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\Models\IAssociation;
use OpenId\Repositories\IOpenIdAssociationRepository;
use OpenId\Services\IAssociationService;
use Models\OpenId\OpenIdAssociation;
use Utils\Db\ITransactionService;
use Utils\Exceptions\UnacquiredLockException;
use Utils\Services\ICacheService;
use Utils\Services\ILockManagerService;

/**
 * Class AssociationService
 * @package Services\OpenId
 */
class AssociationService implements IAssociationService
{
    /**
     * @var ILockManagerService
     */
    private $lock_manager_service;
    /**
     * @var ICacheService
     */
    private $cache_service;
    /**
     * @var IOpenIdAssociationRepository
     */
    private $repository;

    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * AssociationService constructor.
     * @param IOpenIdAssociationRepository $repository
     * @param ILockManagerService $lock_manager_service
     * @param ICacheService $cache_service
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        IOpenIdAssociationRepository $repository,
        ILockManagerService $lock_manager_service,
        ICacheService $cache_service,
        ITransactionService $tx_service
    ) {
        $this->lock_manager_service = $lock_manager_service;
        $this->cache_service        = $cache_service;
        $this->repository           = $repository;
        $this->tx_service           = $tx_service;
    }

    /**
     * gets a given association by handle, and if association exists and its type is private, then lock it
     * to prevent subsequent usage ( private association could be used once)
     * @param $handle
     * @param null $realm
     * @return null|IAssociation
     * @throws ReplayAttackException
     * @throws InvalidAssociation
     * @throws OpenIdInvalidRealmException
     */
    public function getAssociation($handle, $realm = null)
    {

        return $this->tx_service->transaction(function() use($handle, $realm) {

            $lock_name = 'lock.get.assoc.' . $handle;

            try {
                // check if association is on cache
                if (!$this->cache_service->exists($handle)) {
                    // if not , check on db
                    $assoc = $this->repository->getByHandle($handle);
                    if (is_null($assoc)) {
                        throw new InvalidAssociation(sprintf('openid association %s does not exists!', $handle));
                    }
                    //check association lifetime ...
                    $remaining_lifetime = $assoc->getRemainingLifetime();
                    if ($remaining_lifetime < 0) {
                        $this->deleteAssociation($handle);

                        return null;
                    }
                    //convert secret to hexa representation
                    // bin2hex
                    $secret_unpack = \unpack('H*', $assoc->secret);
                    $secret_unpack = array_shift($secret_unpack);
                    //repopulate cache
                    $this->cache_service->storeHash($handle, [
                        "type" => $assoc->type,
                        "mac_function" => $assoc->mac_function,
                        "issued" => $assoc->issued,
                        "lifetime" => $assoc->lifetime,
                        "secret" => $secret_unpack,
                        "realm" => $assoc->realm
                    ], $remaining_lifetime);
                }

                //get hash from cache
                $cache_values = $this->cache_service->getHash($handle, [
                    "type",
                    "mac_function",
                    "issued",
                    "lifetime",
                    "secret",
                    "realm"
                ]);

                if ($cache_values['type'] == IAssociation::TypePrivate) {
                    if (is_null($realm) || empty($realm) || $cache_values['realm'] != $realm) {
                        throw new OpenIdInvalidRealmException(sprintf(OpenIdErrorMessages::InvalidPrivateAssociationMessage,
                            $handle, $realm));
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

                if (!empty($realm)) {
                    $assoc->realm = $realm;
                }

                return $assoc;

            } catch (UnacquiredLockException $ex1) {
                throw new ReplayAttackException
                (
                    sprintf(OpenIdErrorMessages::ReplayAttackPrivateAssociationAlreadyUsed, $handle)
                );
            }
        });
    }

    /**
     * @param $handle
     * @return bool
     */
    public function deleteAssociation($handle)
    {
        return $this->tx_service->transaction(function() use($handle){

            $this->cache_service->delete($handle);
            $assoc = $this->repository->getByHandle($handle);
            if (!is_null($assoc)) {
                return $this->repository->delete($assoc);
            }

            return false;
        });
    }

    /**
     * @param IAssociation $association
     * @return IAssociation
     * @throws ReplayAttackException
     */
    public function addAssociation(IAssociation $association)
    {
        return $this->tx_service->transaction(function() use($association){

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

                if (!is_null($association->getRealm())) {
                    $assoc->realm = $association->getRealm();
                }

                if ($association->getType() == IAssociation::TypeSession) {
                    $this->repository->add($assoc);
                }
                //convert secret to hexa representation
                // bin2hex
                $secret_unpack = \unpack('H*', $association->getSecret());
                $secret_unpack = array_shift($secret_unpack);

                $this->cache_service->storeHash($association->getHandle(),
                    [
                        "type"         => $association->getType(),
                        "mac_function" => $association->getMacFunction(),
                        "issued"       => $association->getIssued(),
                        "lifetime"     => intval($association->getLifetime()),
                        "secret"       => $secret_unpack,
                        "realm"        => !is_null($association->getRealm()) ? $association->getRealm() : ''
                    ],
                    intval($association->getLifetime())
                );

            } catch (UnacquiredLockException $ex1) {
                throw new ReplayAttackException
                (
                    sprintf
                    (
                        OpenIdErrorMessages::ReplayAttackPrivateAssociationAlreadyUsed,
                        $association->getHandle()
                    )
                );
            }

            return $assoc;

        });
    }

}