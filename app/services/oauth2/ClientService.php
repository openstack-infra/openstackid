<?php

namespace services\oauth2;
use oauth2\models\IClient;
use oauth2\services\IClientService;
use Client;
use oauth2\OAuth2Protocol;
use Request;
use Input;

class ClientService implements IClientService{

    /**
     * @param $client_id
     * @return IClient
     */
    public function getClientById($client_id)
    {
        $client = Client::where('client_id', '=', $client_id)->first();
        return $client;
    }

    /**
     *  Clients in possession of a client password MAY use the HTTP Basic
     * authentication scheme as defined in [RFC2617] to authenticate with
     * the authorization server
     * Alternatively, the authorization server MAY support including the
     * client credentials in the request-body using the following
     * parameters:
     * implementation of http://tools.ietf.org/html/rfc6749#section-2.3.1
     * @return list
     */
    public function getCurrentClientAuthInfo()
    {
        //check first http basic auth header
        $auth_header = Request::header('Authorization');
        if(!is_null($auth_header) && !empty($auth_header)){
            $auth_header = trim($auth_header);
            $auth_header = explode(' ',$auth_header);
            $auth_header_content  = $auth_header[1];
            $auth_header_content  = base64_decode($auth_header_content);
            $auth_header_content  = explode(':',$auth_header_content);
            //client_id:client_secret
            return array($auth_header_content[0],$auth_header_content[1]);
        }
        $client_id     = Input::get(OAuth2Protocol::OAuth2Protocol_ClientId,'');
        $client_secret = Input::get(OAuth2Protocol::OAuth2Protocol_ClientSecret,'');
        return array($client_id,$client_secret);
    }

    public function getClientByIdentifier($id)
    {
        // TODO: Implement getClientByIdentifier() method.
    }

    public function addClient($client_id, $client_secret,$client_type, $user_id, $app_name, $app_description, $app_logo=null)
    {
        $client                = new Client;
        $client->app_name      = $app_name;
        $client->app_logo      = $app_logo;
        $client->client_id     = $client_id;
        $client->client_secret = $client_secret;
        $client->client_type   = $client_type;
        $client->user_id       = $user_id;
        $client->active        = true;
        $client->Save();
    }

    public function addClientScope($id, $scope_id)
    {
        // TODO: Implement addClientScope() method.
    }

    public function deleteClientScope($id, $scope_id)
    {
        // TODO: Implement deleteClientScope() method.
    }

    public function addClientAllowedUri($id, $uri)
    {
        // TODO: Implement addClientAllowedUri() method.
    }

    public function deleteClientAllowedUri($id, $uri)
    {
        // TODO: Implement deleteClientAllowedUri() method.
    }

    public function addClientAllowedRealm($id, $realm)
    {
        // TODO: Implement addClientAllowedRealm() method.
    }

    public function deleteClientAllowedRealm($id, $realm)
    {
        // TODO: Implement deleteClientAllowedRealm() method.
    }

    public function deleteClientByIdentifier($id)
    {
        // TODO: Implement deleteClientByIdentifier() method.
    }
}