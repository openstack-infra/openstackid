<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:43 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\handlers;


use openid\exceptions\InvalidDHParam;
use openid\helpers\AssocHandleGenerator;
use openid\OpenIdMessage;
use openid\requests\OpenIdAssociationSessionRequest;
use openid\services\IAssociationService;
use openid\responses\OpenIdDirectGenericErrorResponse;
use openid\requests\OpenIdDHAssociationSessionRequest;
use openid\services\ILogService;
use Zend\Crypt\PublicKey\DiffieHellman;
use Zend\Crypt\Exception\InvalidArgumentException;
use \Zend\Crypt\Exception\RuntimeException;
use openid\helpers\OpenIdCryptoHelper;

/**
 * Class OpenIdSessionAssociationRequestHandler
 * Implements http://openid.net/specs/openid-authentication-2_0.html#associations
 * @package openid\handlers
 */
class OpenIdSessionAssociationRequestHandler extends OpenIdMessageHandler{

    private $association_service;
    private $log;

    public function __construct(IAssociationService $association_service,ILogService $log ,$successor){
        parent::__construct($successor,$log);
        $this->association_service = $association_service;
    }

    protected function InternalHandle(OpenIdMessage $message){
        $this->current_request = null;
        try{

            $this->current_request = new OpenIdDHAssociationSessionRequest($message);

            if(!$this->current_request->IsValid())
                throw new InvalidOpenIdMessageException("Association Session Request is Invalid!");

            $assoc_type       = $this->current_request->getAssocType();
            $session_type     = $this->current_request->getSessionType();
            //DH parameters
            $public_prime     = $this->current_request->getDHModulus();//p
            $public_generator = $this->current_request->getDHGen();//g
            //get (g ^ xa mod p) where xa is rp secret key
            $rp_public_key    = $this->current_request->getDHConsumerPublic();

            $dh               = new DiffieHellman($public_prime, $public_generator);
            $dh->generateKeys();
            //server public key (g ^ xb mod p ), where xb is server private key
            // g ^ (xa * xb) mod p = (g ^ xa) ^ xb mod p = (g ^ xb) ^ xa mod p
            $shared_secret        = $dh->computeSecretKey($rp_public_key,DiffieHellman::FORMAT_NUMBER, DiffieHellman::FORMAT_BTWOC);
            $hashed_shared_secret = OpenIdCryptoHelper::digest($session_type,$shared_secret);
            $HMAC_secret_handle   = OpenIdCryptoHelper::generateSecret($assoc_type);


            $server_public_key    = base64_encode($dh->getPublicKey(DiffieHellman::FORMAT_BTWOC));
            $enc_mac_key          = base64_encode($hashed_shared_secret ^ $HMAC_secret_handle);
            $assoc_handle         = AssocHandleGenerator::generate();
            $expires_in           = 120;
            //save $assoc_handle,$expires_in,$assoc_type(mac func), and $new_secret on storage as session one or public one

        }
        catch(InvalidDHParam $exDH){
            $response  = new OpenIdDirectGenericErrorResponse($exDH->getMessage());
            $this->log->error($exDH);
            return $response;
        }
        catch(InvalidArgumentException $exDH1){
            $response  = new OpenIdDirectGenericErrorResponse($exDH1->getMessage());
            $this->log->error($exDH1);
            return $response;
        }
        catch(RuntimeException $exDH2){
            $response  = new OpenIdDirectGenericErrorResponse($exDH2->getMessage());
            $this->log->error($exDH2);
            return $response;
        }
        catch (InvalidOpenIdMessageException $ex) {
            $response  = new OpenIdDirectGenericErrorResponse($ex->getMessage());
            $this->log->error($ex);
            return $response;
        }
    }


    protected function CanHandle(OpenIdMessage $message)
    {
        $res = OpenIdAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message);
        return $res;
    }
}