<?php
/**
 * Copyright 2015 OpenStack Foundation
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

namespace oauth2\grant_types;

use jwe\IJWE;
use jwk\impl\RSAJWKFactory;
use jwk\impl\RSAJWKPEMPrivateKeySpecification;
use jwk\JSONWebKeyPublicKeyUseValues;
use jws\IJWS;
use oauth2\exceptions\ConsentRequiredException;
use oauth2\exceptions\InteractionRequiredException;
use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\InvalidLoginHint;
use oauth2\exceptions\InvalidOAuth2Request;
use oauth2\exceptions\LockedClientException;
use oauth2\exceptions\LoginRequiredException;
use oauth2\exceptions\OAuth2GenericException;
use oauth2\exceptions\RecipientKeyNotFoundException;
use oauth2\exceptions\ScopeNotAllowedException;
use oauth2\exceptions\UnsupportedResponseTypeException;
use oauth2\exceptions\UriNotAllowedException;
use oauth2\heuristics\ClientSigningKeyFinder;
use oauth2\heuristics\ServerEncryptionKeyFinder;
use oauth2\heuristics\ServerSigningKeyFinder;
use oauth2\models\IClient;
use oauth2\OAuth2Protocol;
use oauth2\repositories\IServerPrivateKeyRepository;
use oauth2\requests\OAuth2AuthenticationRequest;
use oauth2\requests\OAuth2AuthorizationRequest;
use oauth2\requests\OAuth2Request;
use oauth2\services\IApiScopeService;
use oauth2\services\IClientJWKSetReader;
use oauth2\services\IClientService;
use oauth2\services\IMementoOAuth2SerializerService;
use oauth2\services\IPrincipalService;
use oauth2\services\ISecurityContextService;
use oauth2\services\ITokenService;
use oauth2\services\IUserConsentService;
use oauth2\strategies\IOAuth2AuthenticationStrategy;
use oauth2\exceptions\AccessDeniedException;
use utils\factories\BasicJWTFactory;
use utils\services\IAuthService;
use utils\services\ILogService;

/**
 * Class InteractiveGrantType
 * @package oauth2\grant_types
 */
abstract class InteractiveGrantType extends AbstractGrantType
{
    /**
     * @var ISecurityContextService
     */
    protected $security_context_service;

    /**
     * @var IAuthService
     */
    protected $auth_service;

    /**
     * @var IPrincipalService
     */
    protected $principal_service;

    /**
     * @var IOAuth2AuthenticationStrategy
     */
    protected $auth_strategy;
    /**
     * @var IApiScopeService
     */

    protected $scope_service;

    /**
     * @var IUserConsentService
     */
    protected $user_consent_service;

    /**
     * @var IMementoOAuth2SerializerService
     */
    protected $memento_service;


    /**
     * @var IServerPrivateKeyRepository
     */
    private $server_private_key_repository;

    /**
     * @var IClientJWKSetReader
     */
    private $jwk_set_reader_service;

    /**
     * @param IClientService $client_service
     * @param ITokenService $token_service
     * @param ILogService $log_service
     * @param ISecurityContextService $security_context_service
     * @param IPrincipalService $principal_service
     * @param IAuthService $auth_service
     * @param IUserConsentService $user_consent_service
     * @param IApiScopeService $scope_service
     * @param IOAuth2AuthenticationStrategy $auth_strategy
     * @param IMementoOAuth2SerializerService $memento_service
     * @param IServerPrivateKeyRepository $server_private_key_repository
     * @param IClientJWKSetReader $jwk_set_reader_service
     */
    public function __construct
    (
        IClientService                  $client_service,
        ITokenService                   $token_service,
        ILogService                     $log_service,
        ISecurityContextService         $security_context_service,
        IPrincipalService               $principal_service,
        IAuthService                    $auth_service,
        IUserConsentService             $user_consent_service,
        IApiScopeService                $scope_service,
        IOAuth2AuthenticationStrategy   $auth_strategy,
        IMementoOAuth2SerializerService $memento_service,
        IServerPrivateKeyRepository     $server_private_key_repository,
        IClientJWKSetReader             $jwk_set_reader_service
    )
    {
        parent::__construct($client_service, $token_service, $log_service);

        $this->security_context_service      = $security_context_service;
        $this->principal_service             = $principal_service;
        $this->auth_service                  = $auth_service;
        $this->user_consent_service          = $user_consent_service;
        $this->scope_service                 = $scope_service;
        $this->auth_strategy                 = $auth_strategy;
        $this->memento_service               = $memento_service;
        $this->server_private_key_repository = $server_private_key_repository;
        $this->jwk_set_reader_service        = $jwk_set_reader_service;
    }

    public function handle(OAuth2Request $request)
    {
        try
        {

            if (!($request instanceof OAuth2AuthorizationRequest))
            {
                throw new InvalidOAuth2Request;
            }

            $client_id = $request->getClientId();
            $client    = $this->client_service->getClientById($client_id);

            if (is_null($client)) {
                throw new InvalidClientException
                (
                    sprintf
                    (
                        "client_id %s does not exists!",
                        $client_id
                    )
                );
            }

            if (!$client->isActive() || $client->isLocked()) {
                throw new LockedClientException
                (
                    sprintf
                    (
                        'client id %s is locked',
                        $client_id
                    )
                );
            }

            $this->checkClientTypeAccess($client);

            //check redirect uri
            $redirect_uri = $request->getRedirectUri();

            if (!$client->isUriAllowed($redirect_uri)) {
                throw new UriNotAllowedException
                (
                    $redirect_uri
                );
            }

            //check requested scope
            $scope = $request->getScope();

            if (!$client->isScopeAllowed($scope)) {
                throw new ScopeNotAllowedException(sprintf("scope %s", $scope));
            }

            $authentication_response = $this->auth_service->getUserAuthenticationResponse();

            // user has cancelled login action
            if ($authentication_response == IAuthService::AuthenticationResponse_Cancel) {
                //clear saved data ...
                $this->memento_service->forget();
                $this->auth_service->clearUserAuthenticationResponse();
                $this->auth_service->clearUserAuthorizationResponse();

                if ($this->shouldPromptLogin($request)) {
                    throw new LoginRequiredException;
                }

                throw new AccessDeniedException;
            }

            //check user logged
            if ($this->mustAuthenticateUser($request, $client)) {
                if (!$this->canInteractWithEndUser($request)) {
                    throw new LoginRequiredException;
                }

                $this->memento_service->serialize($request->getMessage()->createMemento());

                return $this->auth_strategy->doLogin($request);
            }

            $approval_prompt = $request->getApprovalPrompt();
            $user = $this->auth_service->getCurrentUser();

            // check if logged user its the same as login hint
            $requested_user_id = $this->security_context_service->get()->getRequestedUserId();

            if (is_null($user)) {
                throw new OAuth2GenericException("Invalid Current User");
            }

            if (!is_null($requested_user_id) && $requested_user_id !== $user->getId()) {
                $this->auth_service->logout();
                throw new InvalidLoginHint('invalid login hint');
            }

            $authorization_response = $this->auth_service->getUserAuthorizationResponse();
            //check for former user consents
            $former_user_consent = $this->user_consent_service->get
            (
                $user->getId(),
                $client->getId(),
                $scope
            );

            $auto_approval = $approval_prompt == OAuth2Protocol::OAuth2Protocol_Approval_Prompt_Auto;
            $has_former_consent = !is_null($former_user_consent);
            $should_prompt_consent = $this->shouldPromptConsent($request);

            if ($should_prompt_consent || !($has_former_consent && $auto_approval)) {
                if ($should_prompt_consent || $authorization_response == IAuthService::AuthorizationResponse_None) {
                    if (!$this->canInteractWithEndUser($request)) {
                        throw new InteractionRequiredException;
                    }

                    $this->memento_service->serialize($request->getMessage()->createMemento());

                    return $this->auth_strategy->doConsent($request);
                } else {
                    if ($authorization_response == IAuthService::AuthorizationResponse_DenyOnce) {
                        if ($this->hadPromptConsent($request)) {
                            throw new ConsentRequiredException('the user denied access to your application');
                        }

                        throw new AccessDeniedException;
                    }
                }
                //save possitive consent
                if (is_null($former_user_consent)) {
                    $this->user_consent_service->add($user->getId(), $client->getId(), $scope);
                }
            }
            $this->auth_service->registerRPLogin($client_id);

            $response = $this->buildResponse($request, $has_former_consent);

            // clear save data ...
            $this->auth_service->clearUserAuthorizationResponse();
            $this->memento_service->forget();

            return $response;
        }
        catch(\Exception $ex)
        {
            // clear save data ...
            $this->auth_service->clearUserAuthorizationResponse();
            $this->memento_service->forget();
            throw $ex;
        }
    }

    /**
     * @param string $origin
     * @param string $client_id
     * @param string $session_id
     * @return string
     */
    static public function getSessionState($origin, $client_id, $session_id)
    {
        $salt          = bin2hex(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM));
        $session_state = hash('sha256', "{$client_id}{$origin}{$session_id}{$salt}") . '.' . $salt;
        return $session_state;
    }

    /**
     * @param string $url
     * @return string
     */
    static public function getOrigin($url)
    {
        $url_parts = @parse_url($url);
        return sprintf("%s://%s%s", $url_parts['scheme'], $url_parts['host'], isset($url_parts['port']) ? ':' . $url_parts['port'] : '');
    }

    /**
     * @param OAuth2AuthorizationRequest $request
     * @param  bool $has_former_consent
     * @return OAuth2Response
     */
    abstract protected function buildResponse(OAuth2AuthorizationRequest $request, $has_former_consent);

    /**
     * @param IClient $client
     * @throws InvalidApplicationType
     * @throws InvalidClientType
     * @return void
     */
    abstract protected function checkClientTypeAccess(IClient $client);

    /**
     * @param OAuth2AuthorizationRequest $request
     * @return bool
     */
    protected function canInteractWithEndUser(OAuth2AuthorizationRequest $request)
    {
        if($request instanceof OAuth2AuthenticationRequest && in_array(OAuth2Protocol::OAuth2Protocol_Prompt_None, $request->getPrompt()))
        {
            return false;
        }
        return true;
    }

    /**
     * @param OAuth2AuthenticationRequest $request
     * @return void
     */
    protected function processUserHint(OAuth2AuthenticationRequest $request)
    {
        $login_hint = $request->getLoginHint();
        $token_hint = $request->getIdTokenHint();

        // process login hint
        $user = null;

        if(!empty ($login_hint))
        {

            if (filter_var($login_hint, FILTER_VALIDATE_EMAIL))
            {
                $user = $this->auth_service->getUserByUsername($login_hint);
                if(!$user)
                    throw new InvalidLoginHint('invalid email hint');
            }
            else
            {
                $user_id = $this->auth_service->unwrapUserId($login_hint);
                $user    = $this->auth_service->getUserByExternaldId($user_id);
                if(!$user)
                    throw new InvalidLoginHint('invalid subject hint');
            }

            if(!$user)
                throw new InvalidLoginHint('invalid login hint');

        }

        else if(!empty($token_hint))
        {
            $client_id = $request->getClientId();
            $client    = $this->client_service->getClientById($client_id);
            $user      = null;
            if (is_null($client))
            {
                throw new InvalidClientException
                (
                    sprintf
                    (
                        "client_id %s does not exists!",
                        $client_id
                    )
                );
            }

            $jwt = BasicJWTFactory::build($token_hint);

            if($jwt instanceof IJWE)
            {
                // decrypt using server key

                $recipient_key = RSAJWKFactory::build
                (
                    new RSAJWKPEMPrivateKeySpecification
                    (
                        TestSeeder::$client_private_key_1,
                        RSAJWKPEMPrivateKeySpecification::WithoutPassword,
                        $jwt->getJOSEHeader()->getAlgorithm()->getString()
                    )
                );

                $heuristic              = new ServerEncryptionKeyFinder($this->server_private_key_repository);
                $server_enc_private_key = $heuristic->find
                (
                    $client,
                    $client->getIdTokenResponseInfo()->getEncryptionKeyAlgorithm()
                );

                $jwt->setRecipientKey($server_enc_private_key);

                $payload = $jwt->getPlainText();
                $jwt     = BasicJWTFactory::build($payload);
            }
            if($jwt instanceof IJWS)
            {
                // signed by client ?
                try
                {
                    $heuristic = new ClientSigningKeyFinder($this->jwk_set_reader_service);
                    $client_public_sig_key = $heuristic->find
                    (
                        $client,
                        $client->getIdTokenResponseInfo()->getSigningAlgorithm()
                    );

                    $jwt->setKey($client_public_sig_key);
                }
                catch(RecipientKeyNotFoundException $ex)
                {
                    // try to find the server signing key used ...

                    $heuristic = new ServerSigningKeyFinder($this->server_private_key_repository);
                    $server_private_sig_key = $heuristic->find
                    (
                        $client,
                        $client->getIdTokenResponseInfo()->getSigningAlgorithm(),
                        $jwt->getJOSEHeader()->getKeyID()->getValue()
                    );
                    $jwt->setKey($server_private_sig_key);
                }

                $verified = $jwt->verify($jwt->getJOSEHeader()->getAlgorithm()->getString());

                if(!$verified)
                    throw new InvalidLoginHint('invalid id_token_hint');
            }

            $sub     = $jwt->getClaimSet()->getSubject();
            $user_id = $this->auth_service->unwrapUserId($sub->getString());
            $user    = $this->auth_service->getUserByExternaldId($user_id);

            $session_state = $jwt->getClaimSet()->getClaimByName(OAuth2Protocol::OAuth2Protocol_Session_State);
            if(is_null($session_state)) throw new InvalidLoginHint('invalid session state hint');

            $this->auth_service->reloadSession($session_state->getValue());

            if(!$user)
                throw new InvalidLoginHint('invalid subject hint');
        }

        if($user)
        {
            $principal = $this->principal_service->get();

            if
            (
                !is_null($principal) &&
                !is_null($principal->getUserId()) &&
                $principal->getUserId() !== $user->getId()
            )
            {
                if(!$this->canInteractWithEndUser($request))
                    throw new InteractionRequiredException;

                $this->auth_service->logout();
            }

            $this->security_context_service->save
            (
                $this->security_context_service->get()->setRequestedUserId
                (
                    $user->getId()
                )
            );
        }
    }

    /**
     * @param OAuth2AuthorizationRequest $request
     * @return bool
     */
    protected function shouldPromptLogin(OAuth2AuthorizationRequest $request)
    {
        if
        (
            $request instanceof OAuth2AuthenticationRequest &&
            !$request->isProcessedParam(OAuth2Protocol::OAuth2Protocol_Prompt_Login) &&
            in_array(OAuth2Protocol::OAuth2Protocol_Prompt_Login, $request->getPrompt())
        )
        {
            $request->markParamAsProcessed(OAuth2Protocol::OAuth2Protocol_Prompt_Login);
            return true;
        }
        return false;
    }

    /**
     * @param OAuth2AuthorizationRequest $request
     * @return bool
     */
    protected function shouldPromptConsent(OAuth2AuthorizationRequest $request)
    {
        if
        (
            $request instanceof OAuth2AuthenticationRequest &&
            !$request->isProcessedParam(OAuth2Protocol::OAuth2Protocol_Prompt_Consent) &&
            in_array(OAuth2Protocol::OAuth2Protocol_Prompt_Consent, $request->getPrompt())
        )
        {
            $request->markParamAsProcessed(OAuth2Protocol::OAuth2Protocol_Prompt_Consent);
            return true;
        }
        return false;
    }

    /**
     * @param OAuth2AuthorizationRequest $request
     * @return bool
     */
    protected function hadPromptConsent(OAuth2AuthorizationRequest $request)
    {
        if
        (
            $request instanceof OAuth2AuthenticationRequest &&
            in_array(OAuth2Protocol::OAuth2Protocol_Prompt_Consent, $request->getPrompt())
        )
        {
            return true;
        }
        return false;
    }

    /**
     * @param OAuth2AuthorizationRequest $request
     * @param IClient $client
     * @return bool
     */
    protected function shouldForceReLogin(OAuth2AuthorizationRequest $request, IClient $client)
    {
        $now       = time();
        $principal = $this->principal_service->get();

        if($request instanceof OAuth2AuthenticationRequest)
        {

            $max_age         = $request->getMaxAge();
            $default_max_age = $client->getDefaultMaxAge();

            if(is_null($max_age) && $default_max_age > 0)
                $max_age = $default_max_age;

            if(!is_null($max_age) && $max_age > 0)
            {
                // must required teh auth_time claim
                $this->security_context_service->save
                (
                    $this->security_context_service->get()->setAuthTimeRequired(true)
                );

                if
                (
                    !is_null($principal) &&
                    !is_null($principal->getAuthTime()) &&
                    ($now - $principal->getAuthTime()) > $max_age
                )
                {
                    if (!$this->canInteractWithEndUser($request))
                    {
                        throw new InteractionRequiredException;
                    }

                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param OAuth2AuthorizationRequest $request
     * @param IClient $client
     * @return bool
     * @throws LoginRequiredException
     */
    protected function mustAuthenticateUser(OAuth2AuthorizationRequest $request, IClient $client)
    {

        if($request instanceof OAuth2AuthenticationRequest)
        {
            $this->processUserHint($request);
        }

        if($this->shouldPromptLogin($request))
        {
            $this->auth_service->logout();
            return true;
        }

        if($this->shouldForceReLogin($request, $client))
        {
            $this->auth_service->logout();
            return true;
        }

        if (!$this->auth_service->isUserLogged())
            return true;

        return false;
    }
}