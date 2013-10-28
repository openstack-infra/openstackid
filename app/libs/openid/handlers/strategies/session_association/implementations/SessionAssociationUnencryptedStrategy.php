<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/28/13
 * Time: 6:57 PM
 */

namespace openid\handlers\strategies\implementations;

use openid\exceptions\InvalidDHParam;
use openid\handlers\strategies\ISessionAssociationStrategy;
use openid\requests\OpenIdAssociationSessionRequest;
use openid\responses\OpenIdAssociationSessionResponse;
use openid\responses\OpenIdUnencryptedAssociationSessionResponse;
use Zend\Crypt\Exception\InvalidArgumentException;
use Zend\Crypt\Exception\RuntimeException;
use openid\helpers\OpenIdCryptoHelper;
use openid\model\IAssociation;
use openid\services\Registry;
use openid\services\ServiceCatalog;
use openid\helpers\AssocHandleGenerator;

class SessionAssociationUnencryptedStrategy implements ISessionAssociationStrategy {


    private $association_service;
    private $server_configuration_service;
    private $current_request;
    private $log;

    public function __construct(OpenIdAssociationSessionRequest $request)
    {
        $this->current_request = $request;
        $this->association_service = Registry::getInstance()->get(ServiceCatalog::AssociationService);
        $this->server_configuration_service = Registry::getInstance()->get(ServiceCatalog:: ServerConfigurationService);
        $this->log = Registry::getInstance()->get(ServiceCatalog:: LogService);
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

            $expires_in = $this->server_configuration_service->getSessionAssociationLifetime();
            $response = new OpenIdUnencryptedAssociationSessionResponse($assoc_handle, $session_type, $assoc_type, $expires_in, $HMAC_secret_handle);
            $issued = gmdate("Y-m-d H:i:s", time());
            $this->association_service->addAssociation($assoc_handle, $HMAC_secret_handle, $assoc_type, $expires_in, $issued, IAssociation::TypeSession, null);

        } catch (InvalidDHParam $exDH) {
            $response = new OpenIdDirectGenericErrorResponse($exDH->getMessage());
            $this->log->error($exDH);
        } catch (InvalidArgumentException $exDH1) {
            $response = new OpenIdDirectGenericErrorResponse($exDH1->getMessage());
            $this->log->error($exDH1);

        } catch (RuntimeException $exDH2) {
            $response = new OpenIdDirectGenericErrorResponse($exDH2->getMessage());
            $this->log->error($exDH2);

        }
        return $response;
    }
}