<?php

namespace services\oauth2;

use Client;
use ClientAuthorizedUri;
use DB;
use Input;
use oauth2\exceptions\AllowedClientUriAlreadyExistsException;
use oauth2\exceptions\InvalidClientException;
use oauth2\models\IClient;
use oauth2\OAuth2Protocol;

use oauth2\services\IClientService;
use oauth2\services\OAuth2ServiceCatalog;
use Request;
use utils\services\IAuthService;
use utils\services\Registry;
use Zend\Math\Rand;

/**
 * Class ClientService
 * @package services\oauth2
 */
class ClientService implements IClientService
{

    const PrintableNonWhitespaceCharactersUrl = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_';
    private $auth_service;

    public function __construct(IAuthService $auth_service)
    {
        $this->auth_service = $auth_service;
    }

    /**
     * Clients in possession of a client password MAY use the HTTP Basic
     * authentication scheme as defined in [RFC2617] to authenticate with
     * the authorization server
     * Alternatively, the authorization server MAY support including the
     * client credentials in the request-body using the following
     * parameters:
     * implementation of http://tools.ietf.org/html/rfc6749#section-2.3.1
     * @throws InvalidClientException;
     * @return list
     */
    public function getCurrentClientAuthInfo()
    {
        //check first http basic auth header
        $auth_header = Request::header('Authorization');

        if (!is_null($auth_header) && !empty($auth_header)) {
            $auth_header = trim($auth_header);
            $auth_header = explode(' ', $auth_header);

            if (!is_array($auth_header) || count($auth_header) < 2)
                throw new InvalidClientException;

            $auth_header_content = $auth_header[1];
            $auth_header_content = base64_decode($auth_header_content);
            $auth_header_content = explode(':', $auth_header_content);

            if (!is_array($auth_header_content) || count($auth_header_content) !== 2)
                throw new InvalidClientException;

            //client_id:client_secret
            return array($auth_header_content[0], $auth_header_content[1]);
        }
        //if not get from http input
        $client_id = Input::get(OAuth2Protocol::OAuth2Protocol_ClientId, '');
        $client_secret = Input::get(OAuth2Protocol::OAuth2Protocol_ClientSecret, '');
        return array($client_id, $client_secret);
    }

    public function addClient($client_type, $user_id, $app_name, $app_description, $app_logo = '')
    {
        $instance = null;
        DB::transaction(function () use ($client_type, $user_id, $app_name, $app_description, $app_logo, &$instance) {
            $instance = new Client;
            $instance->app_name = $app_name;
            $instance->app_logo = $app_logo;
            $instance->client_id = Rand::getString(32, self::PrintableNonWhitespaceCharactersUrl, true) . '.openstack.client';
            //only generates secret for confidential clients
            if ($client_type == IClient::ClientType_Confidential)
                $instance->client_secret = Rand::getString(16, self::PrintableNonWhitespaceCharactersUrl, true);
            $instance->client_type = $client_type;
            $instance->user_id = $user_id;
            $instance->active = true;
            $instance->Save();
            //default allowed url
            $this->addClientAllowedUri($instance->getId(), 'https://localhost');
        });
        return $instance;
    }

    public function addClientAllowedUri($id, $uri)
    {
        $client = Client::find($id);
        if (!is_null($client)) {
            $client_uri = ClientAuthorizedUri::where('uri', '=', $uri)->where('client_id', '=', $id)->first();
            if (!is_null($client_uri)) {
                throw new AllowedClientUriAlreadyExistsException(sprintf('uri : %s', $uri));
            }
            $client_authorized_uri = new ClientAuthorizedUri;
            $client_authorized_uri->client_id = $id;
            $client_authorized_uri->uri = $uri;
            $client_authorized_uri->Save();
        }
    }

    public function addClientScope($id, $scope_id)
    {
        $client = Client::find($id);
        if (!is_null($client)) {
            $client->scopes()->attach($scope_id);
        }
    }

    public function deleteClientScope($id, $scope_id)
    {
        $client = Client::find($id);
        if (!is_null($client)) {
            $client->scopes()->detach($scope_id);
        }
    }

    /**
     * Deletes a former client allowed redirection Uri
     * @param $id client identifier
     * @param $uri_id uri identifier
     */
    public function deleteClientAllowedUri($id, $uri_id)
    {
        $uri = ClientAuthorizedUri::where('id', '=', $uri_id)->where('client_id', '=', $id);
        if (!is_null($uri))
            $uri->Delete();
    }

    public function addClientAllowedRealm($id, $realm)
    {
        // TODO: Implement addClientAllowedRealm() method.
    }

    public function deleteClientAllowedRealm($id, $realm_id)
    {
        // TODO: Implement deleteClientAllowedRealm() method.
    }

    public function deleteClientByIdentifier($id)
    {
        $client = Client::find($id);
        if (!is_null($client)) {
            $client->authorized_uris()->delete();
            $client->scopes()->detach();
            $token_service = Registry::getInstance()->get(OAuth2ServiceCatalog::TokenService);
            $token_service->revokeClientRelatedTokens($client->client_id);
            $client->delete();
        }
    }

    /**
     * Regenerates Client Secret
     * @param $id client id
     * @return mixed
     */
    public function regenerateClientSecret($id)
    {
        $new_secret = '';
        DB::transaction(function () use ($id, &$new_secret) {
            $client = Client::find($id);
            if (!is_null($client)) {
                $client_secret = Rand::getString(16, self::PrintableNonWhitespaceCharactersUrl, true);
                $client->client_secret = $client_secret;
                $client->Save();
                $token_service = Registry::getInstance()->get(OAuth2ServiceCatalog::TokenService);
                $token_service->revokeClientRelatedTokens($client->client_id);
                $new_secret = $client->client_secret;
            }
        });
        return $new_secret;
    }

    /**
     * Lock a client application by client id
     * @param $client_id client id
     * @return mixed
     */
    public function lockClient($client_id)
    {
        $client = $this->getClientById($client_id);
        if (!is_null($client)) {
            $client->locked = true;
            $client->Save();
        }
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
        if (!is_null($client)) {
            $client->active = $active;
            $client->Save();
        }
    }

    public function getClientByIdentifier($id)
    {
        $client = Client::where('id', '=', $id)->first();
        return $client;
    }

    public function setRefreshTokenUsage($id, $use_refresh_token)
    {
        $client = $this->getClientByIdentifier($id);
        if (!is_null($client)) {
            $client->use_refresh_token = $use_refresh_token;
            $client->Save();
        }
    }

    public function setRotateRefreshTokenPolicy($id, $rotate_refresh_token)
    {
        $client = $this->getClientByIdentifier($id);
        if (!is_null($client)) {
            $client->rotate_refresh_token = $rotate_refresh_token;
            $client->Save();
        }
    }

    public function existClientAppName($app_name)
    {
        return Client::where('app_name', '=', $app_name)->count() > 0;
    }
}