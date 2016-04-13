<?php namespace OpenId\Handlers\Strategies\Implementations;
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

use OpenId\Exceptions\InvalidDHParam;
use OpenId\Handlers\strategies\ISessionAssociationStrategy;
use OpenId\Requests\OpenIdAssociationSessionRequest;
use OpenId\Responses\OpenIdDirectGenericErrorResponse;
use OpenId\Responses\OpenIdUnencryptedAssociationSessionResponse;
use Zend\Crypt\Exception\InvalidArgumentException;
use Zend\Crypt\Exception\RuntimeException;
//services
use OpenId\Services\IAssociationService;
use OpenId\Services\IServerConfigurationService;
use Utils\Services\ILogService;
use OpenId\Helpers\AssociationFactory;

/**
 * Class SessionAssociationUnencryptedStrategy
 * @package OpenId\Handlers\Strategies\Implementations
 */
class SessionAssociationUnencryptedStrategy implements ISessionAssociationStrategy {

    /**
     * @var IAssociationService
     */
    private $association_service;
    /**
     * @var IServerConfigurationService
     */
    private $server_configuration_service;
    /**
     * @var OpenIdAssociationSessionRequest
     */
    private $current_request;
    /**
     * @var ILogService
     */
    private $log_service;

    /**
     * SessionAssociationUnencryptedStrategy constructor.
     * @param OpenIdAssociationSessionRequest $request
     * @param IAssociationService $association_service
     * @param IServerConfigurationService $server_configuration_service
     * @param ILogService $log_service
     */
    public function __construct
    (
        OpenIdAssociationSessionRequest $request,
        IAssociationService $association_service,
        IServerConfigurationService $server_configuration_service,
        ILogService $log_service
    )
    {
        $this->current_request               = $request;
        $this->association_service           = $association_service;
        $this->server_configuration_service  = $server_configuration_service;
        $this->log_service                   = $log_service;
    }

    /**
     * @return OpenIdDirectGenericErrorResponse
     */
    public function handle()
    {
        $response = null;
        try {
            $assoc_type   = $this->current_request->getAssocType();
            $session_type = $this->current_request->getSessionType();
			$association  = $this->association_service->addAssociation
            (
                AssociationFactory::getInstance()->buildSessionAssociation
                (
                    $assoc_type,
                    $this->server_configuration_service->getConfigValue("Session.Association.Lifetime")
                )
            );

	        $response     = new OpenIdUnencryptedAssociationSessionResponse
            (
                $association->getHandle(),
                $session_type,
                $assoc_type,
                $association->getLifetime(),
                $association->getSecret()
            );

        } catch (InvalidDHParam $exDH) {
            $response = new OpenIdDirectGenericErrorResponse($exDH->getMessage());
            $this->log_service->error($exDH);
        } catch (InvalidArgumentException $exDH1) {
            $response = new OpenIdDirectGenericErrorResponse($exDH1->getMessage());
            $this->log_service->error($exDH1);

        } catch (RuntimeException $exDH2) {
            $response = new OpenIdDirectGenericErrorResponse($exDH2->getMessage());
            $this->log_service->error($exDH2);
        }
        return $response;
    }
}