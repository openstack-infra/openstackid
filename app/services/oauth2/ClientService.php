<?php

namespace services\oauth2;

use Client;
use ClientAuthorizedUri;
use ClientAllowedOrigin;

use DB;
use Input;
use oauth2\exceptions\AllowedClientUriAlreadyExistsException;
use oauth2\exceptions\InvalidClientType;
use oauth2\exceptions\MissingClientAuthorizationInfo;
use oauth2\exceptions\AbsentClientException;

use oauth2\models\IClient;
use oauth2\OAuth2Protocol;
use oauth2\services\IApiScopeService;
use oauth2\services\IApiScope;
use oauth2\services\IClientService;
use oauth2\services\id;
use Request;
use utils\services\IAuthService;
use Zend\Math\Rand;
use Event;
use utils\db\ITransactionService;

/**
 * Class ClientService
 * @package services\oauth2
 */
class ClientService implements IClientService
{
    private $auth_service;
    private $scope_service;

	/**
	 * @param IAuthService        $auth_service
	 * @param IApiScopeService    $scope_service
	 * @param ITransactionService $tx_service
	 */
	public function __construct(IAuthService $auth_service, IApiScopeService $scope_service,ITransactionService $tx_service)
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
     * @throws MissingClientAuthorizationInfo;
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
                throw new MissingClientAuthorizationInfo;

            $auth_header_content = $auth_header[1];
            $auth_header_content = base64_decode($auth_header_content);
            $auth_header_content = explode(':', $auth_header_content);

            if (!is_array($auth_header_content) || count($auth_header_content) !== 2)
                throw new MissingClientAuthorizationInfo;

            //client_id:client_secret
            return array($auth_header_content[0], $auth_header_content[1]);
        }
        //if not get from http input
        $client_id     = Input::get(OAuth2Protocol::OAuth2Protocol_ClientId, '');
        $client_secret = Input::get(OAuth2Protocol::OAuth2Protocol_ClientSecret, '');
        return array($client_id, $client_secret);
    }

    public function addClient($application_type, $user_id, $app_name, $app_description,$app_url=null, $app_logo = '')
    {
        $instance      = null;
	    $this_var      = $this;
	    $scope_service = $this_var->scope_service;

	    $this->tx_service->transaction(function () use ($application_type, $user_id, $app_name,$app_url, $app_description, $app_logo, &$instance, &$this_var,&$scope_service) {

            //check $application_type vs client_type
            $client_type         = $application_type == IClient::ApplicationType_JS_Client? IClient::ClientType_Public : IClient::ClientType_Confidential;
            $instance            = new Client;
            $instance->app_name  = $app_name;
            $instance->app_logo  = $app_logo;
            $instance->client_id = Rand::getString(32, OAuth2Protocol::VsChar, true) . '.openstack.client';
            //only generates secret for confidential clients
            if ($client_type == IClient::ClientType_Confidential)
                $instance->client_secret = Rand::getString(24, OAuth2Protocol::VsChar, true);

            $instance->client_type          = $client_type;
            $instance->application_type     = $application_type;

            $instance->user_id              = $user_id;
            $instance->active               = true;
            $instance->use_refresh_token    = false;
            $instance->rotate_refresh_token = false;
            $instance->website              = $app_url;
            $instance->Save();
            //default allowed url
	        $this_var->addClientAllowedUri($instance->getId(), 'https://localhost');

            //add default scopes
            $default_scopes = $scope_service->getDefaultScopes();

            foreach($default_scopes as $default_scope){
                $instance->scopes()->attach($default_scope->id);
            }

        });
        return $instance;
    }

    public function addClientAllowedUri($id, $uri)
    {
        $res = false;
	    $this->tx_service->transaction(function () use ($id,$uri,&$res){
            $client = Client::find($id);

            if (is_null($client))
                throw new AbsentClientException(sprintf("client id %s does not exists!",$id));

            $client_uri = ClientAuthorizedUri::where('uri', '=', $uri)->where('client_id', '=', $id)->first();
            if (!is_null($client_uri)) {
                throw new AllowedClientUriAlreadyExistsException(sprintf('uri : %s', $uri));
            }

            $client_authorized_uri            = new ClientAuthorizedUri;
            $client_authorized_uri->client_id = $id;
            $client_authorized_uri->uri       = $uri;
            $res                              = $client_authorized_uri->Save();
        });
        return $res;
    }

    public function addClientScope($id, $scope_id)
    {
        $client = Client::find($id);
        if (is_null($client))
            throw new AbsentClientException(sprintf("client id %s does not exists!",$id));
        return $client->scopes()->attach($scope_id);
    }

    public function deleteClientScope($id, $scope_id)
    {
        $client = Client::find($id);
        if (is_null($client))
            throw new AbsentClientException(sprintf("client id %s does not exists!",$id));
        return $client->scopes()->detach($scope_id);
    }

    /**
     * Deletes a former client allowed redirection Uri
     * @param $id client identifier
     * @param $uri_id uri identifier
     */
    public function deleteClientAllowedUri($id, $uri_id)
    {
        return ClientAuthorizedUri::where('id', '=', $uri_id)->where('client_id', '=', $id)->delete();
    }

    public function deleteClientByIdentifier($id)
    {
        $res = false;
	    $this->tx_service->transaction(function () use ($id,&$res){
            $client = Client::find($id);
            if (!is_null($client)) {
                $client->authorized_uris()->delete();
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

            if(is_null($client))
                throw new AbsentClientException(sprintf("client id %d does not exists!.",$id));

            if($client->client_type != IClient::ClientType_Confidential)
                throw new InvalidClientType($id,sprintf("client id %d is not confidential!.",$id));

            $client_secret         = Rand::getString(24, OAuth2Protocol::VsChar, true);
            $client->client_secret = $client_secret;
            $client->Save();
	        Event::fire('oauth2.client.regenerate.secret', array($client->client_id));
            $new_secret            = $client->client_secret;

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
        $res      = false;
	    $this_var = $this;

	    $this->tx_service->transaction(function () use ($client_id, &$res, &$this_var) {

            $client = $this_var->getClientByIdentifier($client_id);
            if (is_null($client))
                throw new AbsentClientException($client_id,sprintf("client id %s does not exists!",$client_id));
            $client->locked = true;
            $res            = $client->Save();
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
        $res      = false;
	    $this_var = $this;

	    $this->tx_service->transaction(function () use ($client_id, &$res, &$this_var) {

            $client = $this_var->getClientByIdentifier($client_id);
            if (is_null($client))
                throw new AbsentClientException($client_id,sprintf("client id %s does not exists!",$client_id));
            $client->locked = false;
            $res            = $client->Save();
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
        if (is_null($client))
            throw new AbsentClientException(sprintf("client id %s does not exists!",$id));
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
        if (is_null($client))
            throw new AbsentClientException(sprintf("client id %s does not exists!",$id));
        $client->use_refresh_token = $use_refresh_token;
        return $client->Save();
    }

    public function setRotateRefreshTokenPolicy($id, $rotate_refresh_token)
    {
        $client = $this->getClientByIdentifier($id);
        if (is_null($client))
            throw new AbsentClientException(sprintf("client id %s does not exists!",$id));

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
    public function getAll($page_nbr=1,$page_size=10,array $filters=array(), array $fields=array('*'))
    {
        DB::getPaginator()->setCurrentPage($page_nbr);
        return Client::Filter($filters)->paginate($page_size,$fields);
    }

    /**
     * @param IClient $client
     * @return bool
     */
    public function save(IClient $client)
    {
        if(!$client->exists() || count($client->getDirty())>0){
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
        $res      = false;
	    $this_var = $this;

	    $this->tx_service->transaction(function () use ($id,$params, &$res, &$this_var) {

            $client = Client::find($id);
            if(is_null($client))
                throw new AbsentClientException(sprintf('client id %s does not exists!',$id));

            $allowed_update_params = array(
                'app_name','website','app_description','app_logo','active','locked','use_refresh_token','rotate_refresh_token');

            foreach($allowed_update_params as $param){
                if(array_key_exists($param,$params)){
                    $client->{$param} = $params[$param];
                }
            }
            $res = $this_var->save($client);
        });
        return $res;
    }

    /**
     * @param $id
     * @param $origin
     * @return mixed
     * @throws \oauth2\exceptions\AllowedClientUriAlreadyExistsException
     * @throws \oauth2\exceptions\AbsentClientException
     */
    public function addClientAllowedOrigin($id, $origin)
    {
        $res = false;

	    $this->tx_service->transaction(function () use ($id, $origin, &$res) {

            $client = Client::find($id);

            if (is_null($client))
                throw new AbsentClientException(sprintf("client id %s does not exists!",$id));

            if($client->getApplicationType()!=IClient::ApplicationType_JS_Client)
                throw new InvalidClientType($id,sprintf("client id %s application type must be JS_CLIENT",$id));

            $client_origin = ClientAllowedOrigin::where('allowed_origin', '=', $origin)->where('client_id', '=', $id)->first();
            if (!is_null($client_origin)) {
                throw new AllowedClientUriAlreadyExistsException(sprintf('origin : %s', $origin));
            }

            $client_origin                 = new ClientAllowedOrigin;
            $client_origin->client_id      = $id;
            $client_origin->allowed_origin = $origin;

            $res =  $client_origin->Save();
        });
        return $res;
    }

    /**
     * @param $id
     * @param $origin_id
     * @return mixed
     */
    public function deleteClientAllowedOrigin($id, $origin_id)
    {
        return ClientAllowedOrigin::where('id', '=', $origin_id)->where('client_id', '=', $id)->delete();
    }
}