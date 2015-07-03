<?php

namespace oauth2\grant_types;

use oauth2\exceptions\InteractionRequiredException;
use oauth2\exceptions\InvalidClientException;
use oauth2\exceptions\InvalidLoginHint;
use oauth2\exceptions\MissingClientIdParam;
use oauth2\exceptions\LockedClientException;
use oauth2\exceptions\InvalidClientCredentials;

use oauth2\models\ClientAuthenticationContext;
use oauth2\models\IClient;
use oauth2\OAuth2Protocol;
use oauth2\requests\OAuth2AuthenticationRequest;
use oauth2\requests\OAuth2AuthorizationRequest;
use oauth2\requests\OAuth2Request;
use oauth2\services\IClientService;

use oauth2\services\IPrincipalService;
use oauth2\services\ISecurityContextService;
use oauth2\services\ITokenService;

use oauth2\strategies\ClientAuthContextValidatorFactory;
use utils\services\IAuthService;
use utils\services\ILogService;


/**
 * Class AbstractGrantType
 * @package oauth2\grant_types
 */
abstract class AbstractGrantType implements IGrantType
{

    /**
     * @var IClientService
     */
    protected $client_service;
    /**
     * @var ITokenService
     */
    protected $token_service;

    /**
     * @var ClientAuthenticationContext
     */
    protected $client_auth_context;
    /**
     * @var IClient
     */
    protected $current_client;
    /**
     * @var ILogService
     */
    protected $log_service;

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
     * @param IClientService $client_service
     * @param ITokenService $token_service
     * @param ILogService $log_service
     * @param ISecurityContextService|null $security_context_service
     * @param IPrincipalService|null $principal_service
     * @param IAuthService|null $auth_service
     */
    public function __construct
    (
        IClientService          $client_service,
        ITokenService           $token_service,
        ILogService             $log_service,
        ISecurityContextService $security_context_service = null,
        IPrincipalService       $principal_service        = null,
        IAuthService            $auth_service             = null
    )
    {
        $this->client_service           = $client_service;
        $this->token_service            = $token_service;
        $this->security_context_service = $security_context_service;
        $this->principal_service        = $principal_service;
        $this->auth_service             = $auth_service;
        $this->log_service              = $log_service;
    }

    /**
     * @param OAuth2Request $request
     * @return mixed|void
     * @throws MissingClientIdParam
     * @throws InvalidClientCredentials
     * @throws InvalidClientException
     * @throws LockedClientException
     */
    public function completeFlow(OAuth2Request $request)
    {
        //get client credentials from request..
        $this->client_auth_context = $this->client_service->getCurrentClientAuthInfo();


        //retrieve client from storage..
        $this->current_client = $this->client_service->getClientById($this->client_auth_context->getId());

        if (is_null($this->current_client))
            throw new InvalidClientException
            (
                sprintf
                (
                    "client id %s does not exists!",
                    $this->client_auth_context->getId()
                )
            );

        if (!$this->current_client->isActive() || $this->current_client->isLocked()) {
            throw new LockedClientException
            (
                sprintf
                (
                    'client id %s is locked.',
                    $this->client_auth_context->getId()
                )
            );
        }

        $this->client_auth_context->setClient($this->current_client);

        if(!ClientAuthContextValidatorFactory::build($this->client_auth_context)->validate($this->client_auth_context))
            throw new InvalidClientCredentials
            (
                sprintf
                (
                    'invalid credentials for client id %s.',
                    $this->client_auth_context->getId()
                )
            );
    }

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
    protected function processLoginHint(OAuth2AuthenticationRequest $request)
    {
        $login_hint = $request->getLoginHint();

        if(!empty ($login_hint))
        {
            // process login hint
            $user = null;
            if (filter_var($login_hint, FILTER_VALIDATE_EMAIL))
            {
                $user = $this->auth_service->getUserByUsername($login_hint);
                if(is_null($user))
                    throw new InvalidLoginHint('invalid email hint');
            }
            else
            {
                $user_id = $this->auth_service->unwrapUserId($login_hint);
                $user    = $this->auth_service->getUserByExternaldId($user_id);
                if(is_null($user))
                    throw new InvalidLoginHint('invalid subject hint');
            }
            if(is_null($user))
                throw new InvalidLoginHint('invalid login hint');


            $principal = $this->principal_service->get();

            if
            (
                !is_null($principal) &&
                !is_null($principal->getUserId()) &&
                $principal->getUserId() !== $user->getExternalIdentifier()
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
                    $user->getExternalIdentifier()
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

                if (!is_null($principal) && ($now - $principal->getAuthTime()) > $max_age)
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
            $this->processLoginHint($request);
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