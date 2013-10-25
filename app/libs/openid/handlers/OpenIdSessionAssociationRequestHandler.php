<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/14/13
 * Time: 5:43 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\handlers;


use openid\OpenIdMessage;
use openid\requests\OpenIdAssociationSessionRequest;
use openid\services\IAssociationService;
use openid\responses\OpenIdDirectGenericErrorResponse;
use openid\requests\OpenIdDHAssociationSessionRequest;
use Zend\Crypt\PublicKey\DiffieHellman;
use Zend\Crypt\Exception\InvalidArgumentException;
use \Zend\Crypt\Exception\RuntimeException;
use openid\helpers\OpenIdCryptoHelper;
use openid\OpenIdProtocol;
/**
 * Class OpenIdSessionAssociationRequestHandler
 * Implements http://openid.net/specs/openid-authentication-2_0.html#associations
 * @package openid\handlers
 */
class OpenIdSessionAssociationRequestHandler extends OpenIdMessageHandler{

    private $association_service;
    private $nonce_service;
    private $current_request;

    public function __construct(IAssociationService $association_service,$successor){
        parent::__construct($successor);
        $this->association_service = $association_service;
    }

    protected function InternalHandle(OpenIdMessage $message){
        $this->current_request = null;
        try{

            //we only implement DH
            $this->current_request = new OpenIdDHAssociationSessionRequest($message);

            if(!$this->current_request->IsValid())
                throw new InvalidOpenIdMessageException("Association Session Request is Invalid!");
            $assoc_type       = $this->current_request->getAssocType();
            $session_type     = $this->current_request->getSessionType();
            //todo: convert $public_prime ,  $public_generator and $rp_public_key to string
            $public_prime     = $this->current_request->getDHModulus();//p
            $public_generator = $this->current_request->getDHGen();//g
            $rp_public_key    = $this->current_request->getDHConsumerPublic();

            $dh               = new DiffieHellman($public_prime, $public_generator);
            $dh->generateKeys();
            $pk               = $dh->getPublicKey();
            $shared_secret    = $dh->computeSecretKey($rp_public_key);
            
            $new_secret       = OpenIdCryptoHelper::generateSecret($assoc_type);
            $shared_secret    = OpenIdCryptoHelper::digest($session_type,$shared_secret);
            $dh_server_public = base64_encode(OpenIdCryptoHelper::btwoc($pk));
            $enc_mac_key      = base64_encode($new_secret ^ $shared_secret);
            $assoc_handle     = uniqid();
            $expires_in       = 120;
            //save $assoc_handle,$expires_in,$assoc_type(mac func), and $new_secret on storage as session one or public one

        }
        catch(InvalidArgumentException $exDH1){
            $response  = new OpenIdDirectGenericErrorResponse($exDH1->getMessage());
            return $response;
        }
        catch(RuntimeException $exDH2){
            $response  = new OpenIdDirectGenericErrorResponse($exDH2->getMessage());
            return $response;
        }
        catch (InvalidOpenIdMessageException $ex) {
            $response  = new OpenIdDirectGenericErrorResponse($ex->getMessage());
            return $response;
        }
    }


    protected function CanHandle(OpenIdMessage $message)
    {
        $res = OpenIdDHAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message);
        return $res;
    }
}