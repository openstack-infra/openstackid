<?php

namespace services\oauth2;

use auth\IUserRepository;
use Client;
use DB;
use Event;
use Input;
use oauth2\exceptions\AbsentClientException;
use oauth2\exceptions\InvalidApiScope;
use oauth2\exceptions\InvalidClientAuthMethodException;
use oauth2\exceptions\InvalidClientType;
use oauth2\exceptions\MissingClientAuthorizationInfo;
use oauth2\models\ClientAssertionAuthenticationContext;
use oauth2\models\ClientAuthenticationContext;
use oauth2\models\ClientCredentialsAuthenticationContext;
use oauth2\models\IClient;
use oauth2\OAuth2Protocol;
use oauth2\services\IApiScope;
use oauth2\services\IApiScopeService;
use oauth2\services\IClientCrendentialGenerator;
use oauth2\services\IClientService;
use oauth2\services\id;
use URL\Normalizer;
use utils\db\ITransactionService;
use utils\exceptions\EntityNotFoundException;
use utils\http\HttpUtils;
use utils\services\IAuthService;
use openid\model\IOpenIdUser;
use oauth2\repositories\IClientRepository;
use oauth2\factories\IOAuth2ClientFactory;
use Request;
/**
 * Class ClientService
 * @package services\oauth2
 */
class ClientService implements IClientService
{
    /**
     * @var IAuthService
     */
    private $auth_service;
    /**
     * @var IApiScopeService
     */
    private $scope_service;

    /**
     * @var IUserRepository
     */
    private $user_repository;
    /**
     * @var IClientCrendentialGenerator
     */
    private $client_credential_generator;

    /**
     * @var IClientRepository
     */
    private $client_repository;

    /**
     * @var IOAuth2ClientFactory
     */
    private $client_factory;

    public function __construct
    (
        IUserRepository             $user_repository,
        IClientRepository           $client_repository,
        IAuthService                $auth_service,
        IApiScopeService            $scope_service,
        IClientCrendentialGenerator $client_credential_generator,
        IOAuth2ClientFactory        $client_factory,
        ITransactionService         $tx_service
    )
    {
        $this->auth_service                = $auth_service;
        $this->user_repository              = $user_repository;
        $this->scope_service               = $scope_service;
        $this->client_credential_generator = $client_credential_generator;
        $this->client_repository           = $client_repository;
        $this->client_factory              = $client_factory;
        $this->tx_service                  = $tx_service;
    }


    /**
     * Clients in possession of a client password MAY use the HTTP Basic
     * authentication scheme as defined in [RFC2617] to authenticate with
     * the authorization server
     * Alternatively, the authorization server MAY support including the
     * client credentials in the request-body using the following
     * parameters:
     * implementation of http://tools.ietf.org/html/rfc6749#section-2.3.1
     * implementation of http://openid.net/specs/openid-connect-core-1_0.html#ClientAuthentication
     * @throws InvalidClientAuthMethodException
     * @throws MissingClientAuthorizationInfo
     * @return ClientAuthenticationContext
     */
    public function getCurrentClientAuthInfo()
    {

        $auth_header = Request::header('Authorization');

        if
        (
            Input::has( OAuth2Protocol::OAuth2Protocol_ClientAssertionType) &&
            Input::has( OAuth2Protocol::OAuth2Protocol_ClientAssertion)
        )
        {
            return new ClientAssertionAuthenticationContext
            (
                Input::get(OAuth2Protocol::OAuth2Protocol_ClientAssertionType, ''),
                Input::get(OAuth2Protocol::OAuth2Protocol_ClientAssertion, '')
            );
        }
        if
        (
            Input::has( OAuth2Protocol::OAuth2Protocol_ClientId) &&
            Input::has( OAuth2Protocol::OAuth2Protocol_ClientSecret)
        )
        {
            return new ClientCredentialsAuthenticationContext
            (
                Input::get(OAuth2Protocol::OAuth2Protocol_ClientId, ''),
                Input::get(OAuth2Protocol::OAuth2Protocol_ClientSecret, ''),
                OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretPost
            );
        }
        if(!empty($auth_header))
        {
            $auth_header = trim($auth_header);
            $auth_header = explode(' ', $auth_header);

            if (!is_array($auth_header) || count($auth_header) < 2)
            {
                throw new MissingClientAuthorizationInfo('bad auth header.');
            }

            $auth_header_content = $auth_header[1];
            $auth_header_content = base64_decode($auth_header_content);
            $auth_header_content = explode(':', $auth_header_content);

            if (!is_array($auth_header_content) || count($auth_header_content) !== 2)
            {
                throw new MissingClientAuthorizationInfo('bad auth header.');
            }

            return new ClientCredentialsAuthenticationContext(
                $auth_header_content[0],
                $auth_header_content[1],
                OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretBasic
            );
        }

        throw new InvalidClientAuthMethodException;
    }

    /**
     * @param string $application_type
     * @param string $app_name
     * @param string $app_description
     * @param null|string  $app_url
     * @param array $admin_users
     * @param string $app_logo
     * @return IClient
     */
    public function addClient
    (
        $application_type,
        $app_name,
        $app_description,
        $app_url = null,
        array $admin_users = array(),
        $app_logo = ''
    )
    {
        $scope_service               = $this->scope_service;
        $client_credential_generator = $this->client_credential_generator;
        $user_repository             = $this->user_repository;
        $client_repository           = $this->client_repository;
        $client_factory              = $this->client_factory;
        $current_user                = $this->auth_service->getCurrentUser();

        return $this->tx_service->transaction(function () use (
            $application_type,
            $current_user,
            $app_name,
            $app_url,
            $app_description,
            $app_logo,
            $admin_users,
            $scope_service,
            $user_repository,
            $client_repository,
            $client_factory,
            $client_credential_generator
        ) {

            $client = $client_factory->build($app_name,$current_user, $application_type);
            $client = $client_credential_generator->generate($client);

            $client->app_logo         = $app_logo;
            $client->app_description  = $app_description;
            $client->website          = $app_url;
            $client_repository->add($client);
            //add default scopes
            $default_scopes = $scope_service->getDefaultScopes();

            foreach ($default_scopes as $default_scope) {
                if
                (
                    $default_scope->name === OAuth2Protocol::OfflineAccess_Scope &&
                    !(
                        $client->application_type == IClient::ApplicationType_Native ||
                        $client->application_type == IClient::ApplicationType_Web_App
                    )
                ) {
                    continue;
                }
                $client->addScope($default_scope);
            }

            //add admin users
            foreach($admin_users as $user_id)
            {
                $user = $user_repository->get(intval($user_id));
                if(is_null($user)) throw new EntityNotFoundException(sprintf('user %s not found.',$user_id));
                $client->addAdminUser($user);
            }

            return $client;
        });
    }


    /**
     * @param $id
     * @param array $params
     * @throws AbsentClientException
     * @throws \ValidationException
     * @return mixed
     */
    public function update($id, array $params)
    {
        $this_var          = $this;
        $client_repository = $this->client_repository;
        $user_repository   = $this->user_repository;
        $editing_user      = $this->auth_service->getCurrentUser();

        return $this->tx_service->transaction(function () use ($id, $editing_user, $params, $client_repository, $user_repository, &$this_var) {

            $client = $client_repository->get($id);

            if (is_null($client)) {
                throw new AbsentClientException(sprintf('client id %s does not exists.', $id));
            }
            $app_name   = isset($params['app_name']) ? trim($params['app_name']) : null;
            if(!empty($app_name)) {
                $old_client = $client_repository->getByApplicationName($app_name);
                if(!is_null($old_client) && $old_client->id !== $client->id)
                    throw new \ValidationException('there is already another application with that name, please choose another one.');
            }
            $current_app_type = $client->getApplicationType();
            if($current_app_type !== $params['application_type'])
            {
                throw new \ValidationException('application type does not match.');
            }

            // validate uris
            switch($current_app_type) {
                case IClient::ApplicationType_Native: {

                    if (isset($params['redirect_uris'])) {
                        $redirect_uris = explode(',', $params['redirect_uris']);
                        //check that custom schema does not already exists for another registerd app
                        if (!empty($params['redirect_uris'])) {
                            foreach ($redirect_uris as $uri) {
                                $uri = @parse_url($uri);
                                if (!isset($uri['scheme'])) {
                                    throw new \ValidationException('invalid scheme on redirect uri.');
                                }
                                if (HttpUtils::isCustomSchema($uri['scheme'])) {
                                    $already_has_schema_registered = Client::where('redirect_uris', 'like',
                                        '%' . $uri['scheme'] . '://%')->where('id', '<>', $id)->count();
                                    if ($already_has_schema_registered > 0) {
                                        throw new \ValidationException(sprintf('schema %s:// already registered for another client.',
                                            $uri['scheme']));
                                    }
                                } else {
                                    if (!HttpUtils::isHttpSchema($uri['scheme'])) {
                                        throw new \ValidationException(sprintf('scheme %s:// is invalid.',
                                            $uri['scheme']));
                                    }
                                }
                            }
                        }
                    }
                }
                    break;
                case IClient::ApplicationType_Web_App:
                case IClient::ApplicationType_JS_Client: {
                    if (isset($params['redirect_uris'])){
                        if (!empty($params['redirect_uris'])) {
                            $redirect_uris = explode(',', $params['redirect_uris']);
                            foreach ($redirect_uris as $uri) {
                                $uri = @parse_url($uri);
                                if (!isset($uri['scheme'])) {
                                    throw new \ValidationException('invalid scheme on redirect uri.');
                                }
                                if (!HttpUtils::isHttpsSchema($uri['scheme'])) {
                                    throw new \ValidationException(sprintf('scheme %s:// is invalid.', $uri['scheme']));
                                }
                            }
                        }
                    }
                    if($current_app_type === IClient::ApplicationType_JS_Client && isset($params['allowed_origins']) &&!empty($params['allowed_origins'])){
                        $allowed_origins = explode(',', $params['allowed_origins']);
                        foreach ($allowed_origins as $uri) {
                            $uri = @parse_url($uri);
                            if (!isset($uri['scheme'])) {
                                throw new \ValidationException('invalid scheme on allowed origin uri.');
                            }
                            if (!HttpUtils::isHttpsSchema($uri['scheme'])) {
                                throw new \ValidationException(sprintf('scheme %s:// is invalid.', $uri['scheme']));
                            }
                        }
                    }
                }
                break;
            }

            $allowed_update_params = array(
                'app_name',
                'website',
                'app_description',
                'app_logo',
                'active',
                'locked',
                'use_refresh_token',
                'rotate_refresh_token',
                'contacts',
                'logo_uri',
                'tos_uri',
                'post_logout_redirect_uris',
                'logout_uri',
                'logout_session_required',
                'logout_use_iframe',
                'policy_uri',
                'jwks_uri',
                'default_max_age',
                'logout_use_iframe',
                'require_auth_time',
                'token_endpoint_auth_method',
                'token_endpoint_auth_signing_alg',
                'subject_type',
                'userinfo_signed_response_alg',
                'userinfo_encrypted_response_alg',
                'userinfo_encrypted_response_enc',
                'id_token_signed_response_alg',
                'id_token_encrypted_response_alg',
                'id_token_encrypted_response_enc',
                'redirect_uris',
                'allowed_origins',
                'admin_users',
            );

            $fields_to_uri_normalize = array
            (
                'post_logout_redirect_uris',
                'logout_uri',
                'policy_uri',
                'jwks_uri',
                'tos_uri',
                'logo_uri',
                'redirect_uris',
                'allowed_origins'
            );

            foreach ($allowed_update_params as $param)
            {

                if (array_key_exists($param, $params))
                {
                    if($param === 'admin_users'){
                        $admin_users = trim($params['admin_users']);
                        $admin_users = empty($admin_users) ? array():explode(',',$admin_users);
                        $client->removeAllAdminUsers();
                        foreach($admin_users as $user_id)
                        {
                            $user = $user_repository->get(intval($user_id));
                            if(is_null($user)) throw new EntityNotFoundException(sprintf('user %s not found.',$user_id));
                            $client->addAdminUser($user);
                        }
                    }
                    else {
                        if (in_array($param, $fields_to_uri_normalize)) {
                            $urls = $params[$param];
                            if (!empty($urls)) {
                                $urls = explode(',', $urls);
                                $normalized_uris = '';
                                foreach ($urls as $url) {
                                    $un = new Normalizer($url);
                                    $url = $un->normalize();
                                    if (!empty($normalized_uris)) {
                                        $normalized_uris .= ',';
                                    }
                                    $normalized_uris .= $url;
                                }
                                $params[$param] = $normalized_uris;
                            }
                        }
                        $client->{$param} = trim($params[$param]);
                    }
                }

            }
            $client_repository->add($client->setEditedBy($editing_user));
            return $client;
        });
   }

    public function addClientScope($id, $scope_id)
    {
        $client = Client::find($id);
        if (is_null($client)) {
            throw new EntityNotFoundException(sprintf("client id %s not found!.", $id));
        }
        $scope = $this->scope_service->get(intval($scope_id));
        if(is_null($scope)) throw new EntityNotFoundException(sprintf("scope %s not found!.", $scope_id));
        $user         = $client->user()->first();

        if($scope->isAssignableByGroups()) {

            $allowed      = false;
            foreach($user->getGroupScopes() as $group_scope)
            {
                if(intval($group_scope->id) === intval($scope_id))
                {
                    $allowed = true; break;
                }
            }
            if(!$allowed) throw new InvalidApiScope(sprintf('you cant assign to this client api scope %s', $scope_id));
        }
        if($scope->isSystem() && !$user->canUseSystemScopes())
            throw new InvalidApiScope(sprintf('you cant assign to this client api scope %s', $scope_id));
        $client->scopes()->attach($scope_id);
        $client->setEditedBy($this->auth_service->getCurrentUser());
        $client->save();
        return $client;
    }

    public function deleteClientScope($id, $scope_id)
    {
        $client = Client::find($id);
        if (is_null($client)) {
            throw new AbsentClientException(sprintf("client id %s does not exists!", $id));
        }
        $client->scopes()->detach($scope_id);
        $client->setEditedBy($this->auth_service->getCurrentUser());
        $client->save();
        return $client;
    }

    public function deleteClientByIdentifier($id)
    {
        $res = false;
        $this->tx_service->transaction(function () use ($id, &$res) {
            $client = Client::find($id);
            if (!is_null($client)) {
                $client->scopes()->detach();
                Event::fire('oauth2.client.delete', array($client->client_id));
                $res = $client->delete();
            }
        });
        return $res;
    }

    /**
     * Regenerates Client Secret
     * @param $id client id
     * @return IClient
     */
    public function regenerateClientSecret($id)
    {
        $client_credential_generator = $this->client_credential_generator;
        $current_user                = $this->auth_service->getCurrentUser();

        return $this->tx_service->transaction(function () use ($id, $current_user, $client_credential_generator)
        {

            $client = Client::find($id);

            if (is_null($client))
            {
                throw new AbsentClientException(sprintf("client id %d does not exists!.", $id));
            }

            if ($client->client_type != IClient::ClientType_Confidential)
            {
                throw new InvalidClientType
                (
                    sprintf
                    (
                        "client id %d is not confidential type!.",
                        $id
                    )
                );
            }

            $client = $client_credential_generator->generate($client, true);
            $client->setEditedBy($current_user);
            $client->save();

            Event::fire('oauth2.client.regenerate.secret', array($client->client_id));
            return $client;
        });
    }

    /**
     * @param client $client_id
     * @return mixed
     * @throws \oauth2\exceptions\AbsentClientException
     */
    public function lockClient($client_id)
    {
        $res = false;
        $this_var = $this;

        $this->tx_service->transaction(function () use ($client_id, &$res, &$this_var) {

            $client = $this_var->getClientByIdentifier($client_id);
            if (is_null($client)) {
                throw new AbsentClientException($client_id, sprintf("client id %s does not exists!", $client_id));
            }
            $client->locked = true;
            $res = $client->Save();
        });

        return $res;
    }

    /**
     * @param client $client_id
     * @return mixed
     * @throws \oauth2\exceptions\AbsentClientException
     */
    public function unlockClient($client_id)
    {
        $res = false;
        $this_var = $this;

        $this->tx_service->transaction(function () use ($client_id, &$res, &$this_var) {

            $client = $this_var->getClientByIdentifier($client_id);
            if (is_null($client)) {
                throw new AbsentClientException($client_id, sprintf("client id %s does not exists!", $client_id));
            }
            $client->locked = false;
            $res = $client->Save();
        });

        return $res;
    }

    /**
     * @param $client_id
     * @return IClient
     */
    public function getClientById($client_id)
    {
        $client = Client::where('client_id', '=', $client_id)->first();

        return $client;
    }

    public function activateClient($id, $active)
    {
        $client = $this->getClientByIdentifier($id);
        if (is_null($client)) {
            throw new AbsentClientException(sprintf("client id %s does not exists!", $id));
        }
        $client->active = $active;

        return $client->Save();
    }

    public function getClientByIdentifier($id)
    {
        $client = Client::where('id', '=', $id)->first();

        return $client;
    }

    public function setRefreshTokenUsage($id, $use_refresh_token)
    {
        $client = $this->getClientByIdentifier($id);
        if (is_null($client)) {
            throw new AbsentClientException(sprintf("client id %s does not exists!", $id));
        }
        $client->use_refresh_token = $use_refresh_token;
        $client->setEditedBy($this->auth_service->getCurrentUser());
        return $client->Save();
    }

    public function setRotateRefreshTokenPolicy($id, $rotate_refresh_token)
    {
        $client = $this->getClientByIdentifier($id);
        if (is_null($client)) {
            throw new AbsentClientException(sprintf("client id %s does not exists!", $id));
        }

        $client->rotate_refresh_token = $rotate_refresh_token;
        $client->setEditedBy($this->auth_service->getCurrentUser());
        return $client->Save();
    }

    public function existClientAppName($app_name)
    {
        return Client::where('app_name', '=', $app_name)->count() > 0;
    }

    /**
     * gets an api scope by id
     * @param $id id of api scope
     * @return IApiScope
     */
    public function get($id)
    {
        return Client::find($id);
    }

    /**
     * @param int $page_nbr
     * @param int $page_size
     * @param array $filters
     * @param array $fields
     * @return mixed
     */
    public function getAll($page_nbr = 1, $page_size = 10, array $filters = array(), array $fields = array('*'))
    {
        DB::getPaginator()->setCurrentPage($page_nbr);

        return Client::Filter($filters)->paginate($page_size, $fields);
    }

    /**
     * @param string $origin
     * @return IClient
     */
    public function getByOrigin($origin)
    {
        return Client::where('allowed_origins', 'like', '%'.$origin.'%')->first();
    }
}