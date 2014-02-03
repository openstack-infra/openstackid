<?php

use oauth2\services\IApiScopeService;
use oauth2\services\IApiService;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use oauth2\services\IResourceServerService;
use oauth2\services\IApiEndpointService;
use utils\services\IAuthService;;

/**
 * Class AdminController
 */
class AdminController extends BaseController {

    private $client_service;
    private $scope_service;
    private $token_service;
    private $resource_server_service;
    private $api_service;
    private $endpoint_service;
    private $auth_service;

    public function __construct( IClientService $client_service,
                                 IApiScopeService $scope_service,
                                 ITokenService $token_service,
                                 IResourceServerService $resource_server_service,
                                 IApiService $api_service,
                                 IApiEndpointService $endpoint_service,
                                 IAuthService $auth_service){

        $this->client_service          = $client_service;
        $this->scope_service           = $scope_service;
        $this->token_service           = $token_service;
        $this->resource_server_service = $resource_server_service;
        $this->api_service             = $api_service;
        $this->endpoint_service        = $endpoint_service;
        $this->auth_service            = $auth_service;
    }

    public function getEditRegisteredClient($id)
    {
        $user   = $this->auth_service->getCurrentUser();
        $client = $this->client_service->getClientByIdentifier($id);

        if (is_null($client)) {
            Log::warning(sprintf("invalid oauth2 client id %s", $id));
            return View::make("404");
        }

        $allowed_uris    = $client->getClientRegisteredUris();
        $selected_scopes = $client->getClientScopes();
        $aux_scopes      = array();

        foreach ($selected_scopes as $scope) {
            array_push($aux_scopes, $scope->id);
        }

        $scopes = $this->scope_service->getAvailableScopes($user->canUseSystemScopes());

        $access_tokens = $this->token_service->getAccessTokenByClient($client->client_id);

        foreach ($access_tokens as $token) {
            $friendly_scopes = $this->scope_service->getFriendlyScopesByName(explode(' ', $token->scope));
            $token->setFriendlyScopes(implode(',', $friendly_scopes));
        }

        $refresh_tokens = $this->token_service->getRefreshTokenByClient($client->client_id);

        foreach ($refresh_tokens as $token) {
            $friendly_scopes = $this->scope_service->getFriendlyScopesByName(explode(' ', $token->scope));
            $token->setFriendlyScopes(implode(',', $friendly_scopes));
        }

        return View::make("oauth2.profile.edit-client",
            array(
                'client'          => $client,
                'allowed_uris'    => $allowed_uris,
                'selected_scopes' => $aux_scopes,
                'scopes'          => $scopes,
                'access_tokens'   => $access_tokens,
                "is_server_admin" => $user->IsServerAdmin(),
                "use_system_scopes" => $user->canUseSystemScopes(),
                'refresh_tokens'  => $refresh_tokens,
            ));
    }

    public function listResourceServers() {
        $resource_servers = $this->resource_server_service->getAll(1,1000);
        return View::make("oauth2.profile.admin.resource-servers",array('resource_servers'=>$resource_servers));
    }

    public function editResourceServer($id){
        $resource_server = $this->resource_server_service->get($id);
        if(is_null($resource_server))
            return View::make('404');
        return View::make("oauth2.profile.admin.edit-resource-server",array('resource_server'=>$resource_server));
    }

    public function editApi($id){
        $api = $this->api_service->get($id);
        if(is_null($api))
            return View::make('404');
        return View::make("oauth2.profile.admin.edit-api",array('api'=>$api));
    }

    public function editScope($id){
        $scope = $this->scope_service->get($id);
        if(is_null($scope))
            return View::make('404');
        return View::make("oauth2.profile.admin.edit-scope",array('scope'=>$scope));
    }

    public function editEndpoint($id){
        $endpoint = $this->endpoint_service->get($id);
        if(is_null($endpoint))
            return View::make('404');
        $selected_scopes = array();
        $list = $endpoint->scopes()->get(array('id'));
        foreach($list as $selected_scope){
            array_push($selected_scopes,$selected_scope->id);
        }
        return View::make("oauth2.profile.admin.edit-endpoint",array(
            'endpoint' => $endpoint ,
            'selected_scopes' => $selected_scopes));
    }

    public function editIssuedGrants(){
        $user           = $this->auth_service->getCurrentUser();
        $access_tokens  = $this->token_service->getAccessTokenByUserId($user->getId());
        $refresh_tokens = $this->token_service->getRefreshTokeByUserId($user->getId());


        foreach($access_tokens as $access_token){
            $friendly_scopes = $this->scope_service->getFriendlyScopesByName(explode(' ',$access_token->scope));
            $access_token->setFriendlyScopes(implode(', ',$friendly_scopes));
        }

        foreach($refresh_tokens as $refresh_token){
            $friendly_scopes = $this->scope_service->getFriendlyScopesByName(explode(' ',$refresh_token->scope));
            $refresh_token->setFriendlyScopes(implode(', ',$friendly_scopes));
        }
        return View::make("oauth2.profile.edit-user-grants",array(
            'access_tokens'   => $access_tokens ,
            'refresh_tokens'  => $refresh_tokens ,
            'is_server_admin' => $user->IsServerAdmin(),
            ));
    }

    public function revokeToken($value){
        $hint = Input::get('hint','none');
        $user = $this->auth_service->getCurrentUser();
        try{
            switch($hint){
                case 'access_token':{
                    $token = $this->token_service->getAccessToken($value,true);
                    if(is_null($token->getUserId()) || intval($token->getUserId())!=intval($user->getId()))
                        throw new Exception(sprintf("access token %s does not belongs to user id %s!.",$value,$user->getId()));
                    $this->token_service->revokeAccessToken($value,true);
                }
                    break;
                case 'refresh_token':
                    $token = $this->token_service->getRefreshToken($value,true);
                    if(is_null($token->getUserId()) || intval($token->getUserId())!=intval($user->getId()))
                        throw new Exception(sprintf("refresh token %s does not belongs to user id %s!.",$value,$user->getId()));
                    $this->token_service->revokeRefreshToken($value,true);
                    break;
                default:
                    throw new Exception(sprintf("hint %s not allowed",$hint));
                    break;
            }
        }
        catch(Exception $ex){
            Log::error($ex);
        }
        return Redirect::action("AdminController@editIssuedGrants");
    }

    public function listOAuth2Clients(){
        $user    = $this->auth_service->getCurrentUser();
        $clients = $user->getClients();

        return View::make("oauth2.profile.clients", array(
            "username" => $user->getFullName(),
            "user_id" => $user->getId(),
            "is_server_admin" => $user->IsServerAdmin(),
            "use_system_scopes" => $user->canUseSystemScopes(),
            'clients' => $clients,
        ));
    }
}