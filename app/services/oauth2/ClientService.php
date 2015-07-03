<?php

namespace services\oauth2;

use Client;
use ClientAllowedOrigin;
use ClientAuthorizedUri;
use DB;
use Event;
use Input;
use oauth2\exceptions\AbsentClientException;
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
use oauth2\services\IClientService;
use oauth2\services\id;
use Request;
use utils\db\ITransactionService;
use utils\services\IAuthService;
use Zend\Math\Rand;

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
     * @param IAuthService $auth_service
     * @param IApiScopeService $scope_service
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        IAuthService $auth_service,
        IApiScopeService $scope_service,
        ITransactionService $tx_service
    )
    {
        $this->auth_service  = $auth_service;
        $this->scope_service = $scope_service;
        $this->tx_service    = $tx_service;
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

    public function addClient($application_type, $user_id, $app_name, $app_description, $app_url = null, $app_logo = '')
    {
        $instance = null;
        $this_var = $this;
        $scope_service = $this_var->scope_service;

        $this->tx_service->transaction(function () use (
            $application_type,
            $user_id,
            $app_name,
            $app_url,
            $app_description,
            $app_logo,
            &$instance,
            &$this_var,
            &$scope_service
        ) {

            $instance = new Client
            (
                array
                (
                    'max_auth_codes_issuance_basis'    => 0,
                    'max_refresh_token_issuance_basis' => 0,
                    'max_access_token_issuance_qty'    => 0,
                    'max_access_token_issuance_basis'  => 0,
                    'max_refresh_token_issuance_qty'   => 0,
                    'use_refresh_token'                => false,
                    'rotate_refresh_token'             => false,
                )
            );

            $instance->app_name        = $app_name;
            $instance->app_logo        = $app_logo;
            $instance->app_description = $app_description;
            $instance->client_id       = Rand::getString(32, OAuth2Protocol::VsChar, true) . '.openstack.client';

            //only generates secret for confidential clients
            $instance->application_type = $application_type;
            if ($instance->client_type === IClient::ClientType_Confidential)
            {
                $instance->token_endpoint_auth_method = OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretBasic;
                $instance->client_secret = Rand::getString(64, OAuth2Protocol::VsChar, true);
            }
            else
            {
                $instance->token_endpoint_auth_method = OAuth2Protocol::TokenEndpoint_AuthMethod_None;
            }

            $instance->user_id              = $user_id;
            $instance->active               = true;
            $instance->use_refresh_token    = false;
            $instance->rotate_refresh_token = false;
            $instance->website              = $app_url;
            $instance->Save();

            //add default scopes
            $default_scopes = $scope_service->getDefaultScopes();

            foreach ($default_scopes as $default_scope)
            {
                if
                (
                    $default_scope->name === OAuth2Protocol::OfflineAccess_Scope &&
                    !(
                        $instance->application_type == IClient::ApplicationType_Native ||
                        $instance->application_type == IClient::ApplicationType_Web_App
                    )
                )
                    continue;
                $instance->scopes()->attach($default_scope->id);
            }

        });

        return $instance;
    }

    public function addClientScope($id, $scope_id)
    {
        $client = Client::find($id);
        if (is_null($client)) {
            throw new AbsentClientException(sprintf("client id %s does not exists!", $id));
        }

        return $client->scopes()->attach($scope_id);
    }

    public function deleteClientScope($id, $scope_id)
    {
        $client = Client::find($id);
        if (is_null($client)) {
            throw new AbsentClientException(sprintf("client id %s does not exists!", $id));
        }

        return $client->scopes()->detach($scope_id);
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
     * @return mixed
     */
    public function regenerateClientSecret($id)
    {
        $new_secret = '';
        $this->tx_service->transaction(function () use ($id, &$new_secret) {

            $client = Client::find($id);

            if (is_null($client)) {
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

            $client_secret = Rand::getString(64, OAuth2Protocol::VsChar, true);
            $client->client_secret = $client_secret;
            $client->Save();
            Event::fire('oauth2.client.regenerate.secret', array($client->client_id));
            $new_secret = $client->client_secret;

        });

        return $new_secret;
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

        return $client->Save();
    }

    public function setRotateRefreshTokenPolicy($id, $rotate_refresh_token)
    {
        $client = $this->getClientByIdentifier($id);
        if (is_null($client)) {
            throw new AbsentClientException(sprintf("client id %s does not exists!", $id));
        }

        $client->rotate_refresh_token = $rotate_refresh_token;

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
     * @param IClient $client
     * @return bool
     */
    public function save(IClient $client)
    {
        if (!$client->exists() || count($client->getDirty()) > 0) {
            return $client->Save();
        }

        return true;
    }

    /**
     * @param $id
     * @param array $params
     * @return bool
     * @throws \oauth2\exceptions\AbsentClientException
     */
    public function update($id, array $params)
    {
        $res = false;
        $this_var = $this;

        $this->tx_service->transaction(function () use ($id, $params, &$res, &$this_var) {

            $client = Client::find($id);
            if (is_null($client)) {
                throw new AbsentClientException(sprintf('client id %s does not exists!', $id));
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
            );

            foreach ($allowed_update_params as $param)
            {
                if (array_key_exists($param, $params))
                {
                    $client->{$param} = $params[$param];
                }
            }
            $res = $this_var->save($client);
        });

        return $res;
    }
}