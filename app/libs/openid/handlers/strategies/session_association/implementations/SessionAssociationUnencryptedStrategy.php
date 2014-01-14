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
use openid\services\OpenIdServiceCatalog;
use utils\services\Registry;
use utils\services\UtilsServiceCatalog;
use Zend\Crypt\Exception\InvalidArgumentException;
use Zend\Crypt\Exception\RuntimeException;

class SessionAssociationUnencryptedStrategy implements ISessionAssociationStrategy {


    private $association_service;
    private $server_configuration_service;
    private $current_request;
    private $log_service;

    public function __construct(OpenIdAssociationSessionRequest $request)
    {
        $this->current_request               = $request;
        $this->association_service           = Registry::getInstance()->get(OpenIdServiceCatalog::AssociationService);
        $this->server_configuration_service  = Registry::getInstance()->get(OpenIdServiceCatalog:: ServerConfigurationService);
        $this->log_service                   = Registry::getInstance()->get(UtilsServiceCatalog:: LogService);
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

            $HMAC_secret_handle = OpenIdCryptoHelper::generateSecret($assoc_type);

            $assoc_handle = AssocHandleGenerator::generate();

            $expires_in = $this->server_configuration_service->getConfigValue("Session.Association.Lifetime");

            $response = new OpenIdUnencryptedAssociationSessionResponse($assoc_handle, $session_type, $assoc_type, $expires_in, $HMAC_secret_handle);
            $issued = gmdate("Y-m-d H:i:s", time());
            $this->association_service->addAssociation($assoc_handle, $HMAC_secret_handle, $assoc_type, $expires_in, $issued, IAssociation::TypeSession, null);

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