<?php

use oauth2\services\IApiScopeService;
use oauth2\services\IApiService;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use oauth2\services\IResourceServerService;
use oauth2\services\IApiEndpointService;
use utils\services\IAuthService;
use openid\services\IUserService;
use utils\services\IServerConfigurationService;
use \utils\services\IBannedIPService;
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
    private $user_service;
    private $configuration_service;
    private $banned_ips_service;

    public function __construct( IClientService $client_service,
                                 IApiScopeService $scope_service,
                                 ITokenService $token_service,
                                 IResourceServerService $resource_server_service,
                                 IApiService $api_service,
                                 IApiEndpointService $endpoint_service,
                                 IAuthService $auth_service,
                                 IUserService $user_service,
                                 IServerConfigurationService $configuration_service,
                                 IBannedIPService $banned_ips_service){

        $this->client_service          = $client_service;
        $this->scope_service           = $scope_service;
        $this->token_service           = $token_service;
        $this->resource_server_service = $resource_server_service;
        $this->api_service             = $api_service;
        $this->endpoint_service        = $endpoint_service;
        $this->auth_service            = $auth_service;
        $this->user_service            = $user_service;
        $this->configuration_service   = $configuration_service;
        $this->banned_ips_service      = $banned_ips_service;
    }

    public function editRegisteredClient($id)
    {
        $user   = $this->auth_service->getCurrentUser();
        $client = $this->client_service->getClientByIdentifier($id);

        if (is_null($client)) {
            Log::warning(sprintf("invalid oauth2 client id %s", $id));
            return View::make("404");
        }

        $allowed_uris    = $client->getClientRegisteredUris();
        $allowed_origins = $client->getClientAllowedOrigins();
        $selected_scopes = $client->getClientScopes();
        $aux_scopes      = array();

        foreach ($selected_scopes as $scope) {
            array_push($aux_scopes, $scope->id);
        }

        $scopes        = $this->scope_service->getAvailableScopes($user->canUseSystemScopes());

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
                'allowed_origins' => $allowed_origins,
                'selected_scopes' => $aux_scopes,
                'scopes'          => $scopes,
                'access_tokens'   => $access_tokens,
                "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
                "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
                "use_system_scopes" => $user->canUseSystemScopes(),
                'refresh_tokens'  => $refresh_tokens,
            ));
    }

    public function listResourceServers() {
        $user   = $this->auth_service->getCurrentUser();
        $resource_servers = $this->resource_server_service->getAll(1,1000);
        return View::make("oauth2.profile.admin.resource-servers",array(
            "is_oauth2_admin"      => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            'resource_servers'=>$resource_servers));
    }

    public function editResourceServer($id){
        $resource_server = $this->resource_server_service->get($id);
        if(is_null($resource_server))
            return View::make('404');
        $user   = $this->auth_service->getCurrentUser();
        return View::make("oauth2.profile.admin.edit-resource-server",array(
            "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            'resource_server'=>$resource_server
        ));
    }

    public function editApi($id){
        $api = $this->api_service->get($id);
        if(is_null($api))
            return View::make('404');
        $user   = $this->auth_service->getCurrentUser();
        return View::make("oauth2.profile.admin.edit-api",array(
            "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            'api'=>$api));
    }

    public function editScope($id){
        $scope = $this->scope_service->get($id);
        if(is_null($scope))
            return View::make('404');
        $user   = $this->auth_service->getCurrentUser();
        return View::make("oauth2.profile.admin.edit-scope",array(
            "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            'scope'=>$scope));
    }

    public function editEndpoint($id){
        $endpoint = $this->endpoint_service->get($id);
        if(is_null($endpoint))
            return View::make('404');
        $user   = $this->auth_service->getCurrentUser();
        $selected_scopes = array();
        $list = $endpoint->scopes()->get(array('id'));
        foreach($list as $selected_scope){
            array_push($selected_scopes,$selected_scope->id);
        }
        return View::make("oauth2.profile.admin.edit-endpoint",array(
            "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
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
            'user_id'              => $user->getId(),
            'access_tokens'        => $access_tokens ,
            'refresh_tokens'       => $refresh_tokens ,
            "is_oauth2_admin"      => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            ));
    }

    public function listOAuth2Clients(){
        $user    = $this->auth_service->getCurrentUser();
        $clients = $user->getClients();

        return View::make("oauth2.profile.clients", array(
            "username" => $user->getFullName(),
            "user_id" => $user->getId(),
            "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            "use_system_scopes" => $user->canUseSystemScopes(),
            'clients' => $clients,
        ));
    }

    public function listLockedClients(){
        $user    = $this->auth_service->getCurrentUser();
        $clients = $this->client_service->getAll(1,1000,array(
            array(
                'name'=>'locked',
                'op' => '=',
                'value'=> true
            )
        ));

        return View::make("oauth2.profile.admin.clients", array(
            "username" => $user->getFullName(),
            "user_id" => $user->getId(),
            "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            'clients' => $clients,
        ));
    }

    public function listLockedUsers(){
        $user    = $this->auth_service->getCurrentUser();
        $users   = $this->user_service->getAll(1,1000,array(
            array(
                'name'=>'lock',
                'op' => '=',
                'value'=> true
            )
        ));

        return View::make("admin.users", array(
            "username" => $user->getFullName(),
            "user_id" => $user->getId(),
            "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            'users' => $users,
        ));
    }



    public function listServerConfig(){

        $user    = $this->auth_service->getCurrentUser();
        $config_values = array();

        $config_values['MaxFailed.Login.Attempts'] = $this->configuration_service->getConfigValue('MaxFailed.Login.Attempts');
        $config_values['MaxFailed.LoginAttempts.2ShowCaptcha'] = $this->configuration_service->getConfigValue('MaxFailed.LoginAttempts.2ShowCaptcha');

        $config_values['OpenId.Private.Association.Lifetime'] = $this->configuration_service->getConfigValue('OpenId.Private.Association.Lifetime');
        $config_values['OpenId.Session.Association.Lifetime'] = $this->configuration_service->getConfigValue('OpenId.Session.Association.Lifetime');
        $config_values['OpenId.Nonce.Lifetime'] = $this->configuration_service->getConfigValue('OpenId.Nonce.Lifetime');

        $config_values['OAuth2.AuthorizationCode.Lifetime'] = $this->configuration_service->getConfigValue('OAuth2.AuthorizationCode.Lifetime');
        $config_values['OAuth2.AccessToken.Lifetime'] = $this->configuration_service->getConfigValue('OAuth2.AccessToken.Lifetime');
        $config_values['OAuth2.RefreshToken.Lifetime'] = $this->configuration_service->getConfigValue('OAuth2.RefreshToken.Lifetime');

        return View::make("admin.server-config", array(
            "username" => $user->getFullName(),
            "user_id" => $user->getId(),
            "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            'config_values' => $config_values,
        ));
    }

    public function saveServerConfig(){

        $values = Input::all();

        $rules = array(
            'general-max-failed-login-attempts'         => 'required|integer',
            'general-max-failed-login-attempts-captcha' => 'required|integer',
            'openid-private-association-lifetime'       => 'required|integer',
            'openid-session-association-lifetime'       => 'required|integer',
            'openid-nonce-lifetime'                     => 'required|integer',
            'oauth2-auth-code-lifetime'                 => 'required|integer',
            'oauth2-refresh-token-lifetime'             => 'required|integer',
            'oauth2-access-token-lifetime'              => 'required|integer',
        );

        $dictionary = array(
            'general-max-failed-login-attempts'         => 'MaxFailed.Login.Attempts',
            'general-max-failed-login-attempts-captcha' => 'MaxFailed.LoginAttempts.2ShowCaptcha',
            'openid-private-association-lifetime'       => 'OpenId.Private.Association.Lifetime',
            'openid-session-association-lifetime'       => 'OpenId.Session.Association.Lifetime',
            'openid-nonce-lifetime'                     => 'OpenId.Nonce.Lifetime',
            'oauth2-auth-code-lifetime'                 => 'OAuth2.AuthorizationCode.Lifetime',
            'oauth2-access-token-lifetime'              => 'OAuth2.AccessToken.Lifetime',
            'oauth2-refresh-token-lifetime'             => 'OAuth2.RefreshToken.Lifetime',
        );

        // Creates a Validator instance and validates the data.
        $validation = Validator::make($values, $rules);

        if ($validation->fails()) {
            return Redirect::action("AdminController@listServerConfig")->withErrors($validation);
        }

        foreach($values as $field=>$value){
            if(array_key_exists($field,$dictionary))
                $this->configuration_service->saveConfigValue($dictionary[$field],$value);
        }

        return Redirect::action("AdminController@listServerConfig");
    }

    public function listBannedIPs(){
        $user    = $this->auth_service->getCurrentUser();
        $ips     = $this->banned_ips_service->getByPage(1,1000);
        return View::make("admin.banned-ips", array(
            "username" => $user->getFullName(),
            "user_id" => $user->getId(),
            "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            "ips" =>$ips
        ));
    }
}