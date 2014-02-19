<?php

namespace openid\handlers\strategies\implementations;

use openid\handlers\strategies\ISessionAssociationStrategy;
use openid\helpers\OpenIdCryptoHelper;
use openid\model\IAssociation;
use openid\requests\OpenIdDHAssociationSessionRequest;
use openid\responses\OpenIdDiffieHellmanAssociationSessionResponse;
use Zend\Crypt\PublicKey\DiffieHellman;
//services
use openid\services\IAssociationService;
use openid\services\IServerConfigurationService;
use utils\services\ILogService;
use openid\helpers\AssociationFactory;

class SessionAssociationDHStrategy implements ISessionAssociationStrategy
{

    private $association_service;
    private $server_configuration_service;
    private $current_request;
    private $log_service;

	/**
	 * @param OpenIdDHAssociationSessionRequest $request
	 * @param IAssociationService               $association_service
	 * @param IServerConfigurationService       $server_configuration_service
	 * @param ILogService                       $log_service
	 */
	public function __construct(OpenIdDHAssociationSessionRequest $request,
                                IAssociationService $association_service,
                                IServerConfigurationService $server_configuration_service,
								ILogService $log_service)
    {
        $this->current_request              = $request;
        $this->association_service          = $association_service;
        $this->server_configuration_service = $server_configuration_service;
        $this->log_service                  = $log_service;
    }

    /**
     * @return null|OpenIdDirectGenericErrorResponse|\openid\responses\OpenIdAssociationSessionResponse|OpenIdDiffieHellmanAssociationSessionResponse
     */
    public function handle()
    {
        $response = null;
        try {
            $assoc_type       = $this->current_request->getAssocType();
            $session_type     = $this->current_request->getSessionType();
            //DH parameters
            $public_prime     = $this->current_request->getDHModulus(); //p
            $public_generator = $this->current_request->getDHGen(); //g
            //get (g ^ xa mod p) where xa is rp secret key
            $rp_public_key    = $this->current_request->getDHConsumerPublic();
	        //create association
	        $association      = $this->association_service->addAssociation(AssociationFactory::getInstance()->buildSessionAssociation($assoc_type, $this->server_configuration_service->getConfigValue("Session.Association.Lifetime")));
	        $dh               = new DiffieHellman($public_prime, $public_generator);
	        $dh->generateKeys();
	        //server public key (g ^ xb mod p ), where xb is server private key
	        // g ^ (xa * xb) mod p = (g ^ xa) ^ xb mod p = (g ^ xb) ^ xa mod p
	        $shared_secret        = $dh->computeSecretKey($rp_public_key, DiffieHellman::FORMAT_NUMBER, DiffieHellman::FORMAT_BTWOC);
	        $hashed_shared_secret = OpenIdCryptoHelper::digest($session_type, $shared_secret);
	        $server_public_key    = base64_encode($dh->getPublicKey(DiffieHellman::FORMAT_BTWOC));
	        $enc_mac_key          = base64_encode($association->getSecret()  ^ $hashed_shared_secret);

	        $response             = new OpenIdDiffieHellmanAssociationSessionResponse($association->getHandle(), $session_type, $assoc_type, $association->getLifetime(), $server_public_key, $enc_mac_key);

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