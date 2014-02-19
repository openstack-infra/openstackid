<?php

namespace openid\handlers\strategies\implementations;

use openid\exceptions\InvalidDHParam;
use openid\handlers\strategies\ISessionAssociationStrategy;
use openid\helpers\AssocHandleGenerator;
use openid\helpers\OpenIdCryptoHelper;
use openid\model\IAssociation;
use openid\requests\OpenIdAssociationSessionRequest;
use openid\responses\OpenIdAssociationSessionResponse;
use openid\responses\OpenIdUnencryptedAssociationSessionResponse;
use Zend\Crypt\Exception\InvalidArgumentException;
use Zend\Crypt\Exception\RuntimeException;

//services
use openid\services\IAssociationService;
use openid\services\IServerConfigurationService;
use utils\services\ILogService;
use openid\helpers\AssociationFactory;

/**
 * Class SessionAssociationUnencryptedStrategy
 * @package openid\handlers\strategies\implementations
 */
class SessionAssociationUnencryptedStrategy implements ISessionAssociationStrategy {


    private $association_service;
    private $server_configuration_service;
    private $current_request;
    private $log_service;

    public function __construct(OpenIdAssociationSessionRequest $request,
                                IAssociationService $association_service,
                                IServerConfigurationService $server_configuration_service,
                                ILogService $log_service)
    {
        $this->current_request               = $request;
        $this->association_service           = $association_service;
        $this->server_configuration_service  = $server_configuration_service;
        $this->log_service                   = $log_service;
    }

    /**
     * @return null|OpenIdDirectGenericErrorResponse|OpenIdAssociationSessionResponse|OpenIdUnencryptedAssociationSessionResponse
     */
    public function handle()
    {
        $response = null;
        try {
            $assoc_type   = $this->current_request->getAssocType();
            $session_type = $this->current_request->getSessionType();
			$association  = $this->association_service->addAssociation(AssociationFactory::getInstance()->buildSessionAssociation($assoc_type,$this->server_configuration_service->getConfigValue("Session.Association.Lifetime")));
	        $response     = new OpenIdUnencryptedAssociationSessionResponse($association->getHandle() , $session_type, $assoc_type, $association->getLifetime(), $association->getSecret());

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