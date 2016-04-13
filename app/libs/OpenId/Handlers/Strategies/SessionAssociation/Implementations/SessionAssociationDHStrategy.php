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
use OpenId\Handlers\Strategies\ISessionAssociationStrategy;
use OpenId\Helpers\OpenIdCryptoHelper;
use OpenId\Requests\OpenIdDHAssociationSessionRequest;
use OpenId\Responses\OpenIdDiffieHellmanAssociationSessionResponse;
use OpenId\Responses\OpenIdDirectGenericErrorResponse;
use OpenId\Responses\OpenIdResponse;
use Zend\Crypt\PublicKey\DiffieHellman;
//services
use OpenId\Services\IAssociationService;
use OpenId\Services\IServerConfigurationService;
use Utils\Services\ILogService;
use OpenId\Helpers\AssociationFactory;
use OpenId\Exceptions\InvalidDHParam;
use Zend\Crypt\Exception\InvalidArgumentException;
use Zend\Crypt\Exception\RuntimeException;

/**
 * Class SessionAssociationDHStrategy
 * @package OpenId\Handlers\Strategies\Implementations
 */
class SessionAssociationDHStrategy implements ISessionAssociationStrategy
{

    /**
     * @var IAssociationService
     */
    private $association_service;
    /**
     * @var IServerConfigurationService
     */
    private $server_configuration_service;
    /**
     * @var OpenIdDHAssociationSessionRequest
     */
    private $current_request;
    /**
     * @var ILogService
     */
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
     * @return OpenIdResponse
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
	        $association      = $this->association_service->addAssociation(AssociationFactory::getInstance()
                ->buildSessionAssociation
                (
                    $assoc_type,
                    $this->server_configuration_service->getConfigValue("Session.Association.Lifetime")
                )
            );

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