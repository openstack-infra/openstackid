<?php namespace App\Http\Controllers;
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
use Auth\Repositories\IUserRepository;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use OAuth2\Repositories\IAccessTokenRepository;
use OAuth2\Repositories\IApiEndpointRepository;
use OAuth2\Repositories\IApiRepository;
use OAuth2\Repositories\IApiScopeRepository;
use OAuth2\Repositories\IClientRepository;
use OAuth2\Repositories\IRefreshTokenRepository;
use OAuth2\Repositories\IResourceServerRepository;
use OAuth2\Services\ITokenService;
use OAuth2\Repositories\IApiScopeGroupRepository;
use OAuth2\Repositories\IServerPrivateKeyRepository;
use OAuth2\Services\IApiEndpointService;
use OAuth2\Services\IApiScopeService;
use OAuth2\Services\IApiService;
use OAuth2\Services\IClientService;
use OAuth2\Services\IResourceServerService;
use OpenId\Services\IUserService;
use Utils\Services\IAuthService;
use Utils\Services\IBannedIPService;
use Utils\Services\IServerConfigurationService;
use Illuminate\Support\Facades\Log;

/**
 * Class AdminController
 * @package App\Http\Controllers
 */
class AdminController extends Controller {

    /**
     * @var IClientService
     */
    private $client_service;
    /**
     * @var IApiScopeService
     */
    private $scope_service;

    /**
     * @var IAccessTokenRepository
     */
    private $access_token_repository;

    /**
     * @var IRefreshTokenRepository
     */
    private $refresh_token_repository;

    /**
     * @var IResourceServerService
     */
    private $resource_server_service;
    /**
     * @var IApiService
     */
    private $api_service;
    /**
     * @var IApiEndpointService
     */
    private $endpoint_service;
    /**
     * @var IAuthService
     */
    private $auth_service;
    /**
     * @var IUserService
     */
    private $user_service;
    /**
     * @var IServerConfigurationService
     */
    private $configuration_service;
    /**
     * @var IBannedIPService
     */
    private $banned_ips_service;

    /**
     * @var IServerPrivateKeyRepository
     */
    private $private_keys_repository;

    /**
     * @var IApiScopeGroupRepository
     */
    private $group_repository;

    /**
     * @var IClientRepository
     */
    private $client_repository;

    /**
     * @var IUserRepository
     */
    private $user_repository;

    /**
     * @var IApiEndpointRepository
     */
    private $endpoint_repository;

    /**
     * @var IApiScopeRepository
     */
    private $scope_repository;

    /**
     * @var IApiRepository
     */
    private $api_repository;

    /**
     * @var IResourceServerRepository
     */
    private $resource_server_repository;

    const TokenPageSize = 25;

    public function __construct(
        IClientService $client_service,
        IApiScopeService $scope_service,
        IAccessTokenRepository $access_token_repository,
        IRefreshTokenRepository $refresh_token_repository,
        IResourceServerService $resource_server_service,
        IApiService $api_service,
        IApiEndpointService $endpoint_service,
        IAuthService $auth_service,
        IUserService $user_service,
        IServerConfigurationService $configuration_service,
        IBannedIPService $banned_ips_service,
        IServerPrivateKeyRepository $private_keys_repository,
        IApiScopeGroupRepository $group_repository,
        IClientRepository $client_repository,
        IUserRepository $user_repository,
        IApiEndpointRepository $endpoint_repository,
        IApiScopeRepository $scope_repository,
        IApiRepository $api_repository,
        IResourceServerRepository $resource_server_repository
    )
    {

        $this->client_service             = $client_service;
        $this->scope_service              = $scope_service;
        $this->access_token_repository    = $access_token_repository;
        $this->refresh_token_repository   = $refresh_token_repository;
        $this->resource_server_service    = $resource_server_service;
        $this->api_service                = $api_service;
        $this->endpoint_service           = $endpoint_service;
        $this->auth_service               = $auth_service;
        $this->user_service               = $user_service;
        $this->configuration_service      = $configuration_service;
        $this->banned_ips_service         = $banned_ips_service;
        $this->private_keys_repository    = $private_keys_repository;
        $this->group_repository           = $group_repository;
        $this->client_repository          = $client_repository;
        $this->user_repository            = $user_repository;
        $this->endpoint_repository        = $endpoint_repository;
        $this->scope_repository           = $scope_repository;
        $this->api_repository             = $api_repository;
        $this->resource_server_repository = $resource_server_repository;
    }

    public function editRegisteredClient($id)
    {
        $user    = $this->auth_service->getCurrentUser();
        $client  = $this->client_repository->getClientByIdentifier($id);

        if (is_null($client)) {
            Log::warning(sprintf("invalid oauth2 client id %s", $id));
            return View::make("errors.404");
        }

        $selected_scopes = $client->getClientScopes();
        $aux_scopes      = array();

        foreach ($selected_scopes as $scope) {
            array_push($aux_scopes, $scope->id);
        }

        $scopes        = $this->scope_service->getAvailableScopes();
        $group_scopes  = $user->getGroupScopes();

        $access_tokens = $this->access_token_repository->getAllValidByClientIdentifier($client->getId(), 1 , self::TokenPageSize);

        foreach ($access_tokens->items() as $token) {
            $friendly_scopes = $this->scope_service->getFriendlyScopesByName(explode(' ', $token->scope));
            $token->setFriendlyScopes(implode(',', $friendly_scopes));
        }

        $refresh_tokens = $this->refresh_token_repository->getAllValidByClientIdentifier($client->getId(), 1 , self::TokenPageSize);

        foreach ($refresh_tokens->items() as $token) {
            $friendly_scopes = $this->scope_service->getFriendlyScopesByName(explode(' ', $token->scope));
            $token->setFriendlyScopes(implode(',', $friendly_scopes));
        }

        return View::make("oauth2.profile.edit-client",
            [
                'client'               => $client,
                'selected_scopes'      => $aux_scopes,
                'scopes'               => array_merge($scopes, $group_scopes),
                'access_tokens'        => $access_tokens->items(),
                'access_tokens_pages'  => $access_tokens->total() > 0 ? intval(ceil($access_tokens->total() / self::TokenPageSize)) : 0,
                "is_oauth2_admin"      => $user->isOAuth2ServerAdmin(),
                "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
                "use_system_scopes"    => $user->canUseSystemScopes(),
                'refresh_tokens'       => $refresh_tokens->items(),
                'refresh_tokens_pages' => $refresh_tokens->total() > 0 ? intval(ceil($refresh_tokens->total() / self::TokenPageSize)) : 0,
            ]);
    }

    // Api Scope Groups

    public function listApiScopeGroups()
    {
        $user                = $this->auth_service->getCurrentUser();
        $groups              = $this->group_repository->getAll(1, PHP_INT_MAX);
        $non_selected_scopes = $this->scope_service->getAssignedByGroups();
        return View::make("oauth2.profile.admin.api-scope-groups",array
        (
            "is_oauth2_admin"      => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            'groups'               => $groups,
            'non_selected_scopes'  => $non_selected_scopes,
        ));
    }

    public function editApiScopeGroup($id){
        $group = $this->group_repository->get($id);

        if(is_null($group))
            return Response::view('errors.404', array(), 404);
        $user   = $this->auth_service->getCurrentUser();
        $non_selected_scopes = $this->scope_service->getAssignedByGroups();
        return View::make("oauth2.profile.admin.edit-api-scope-group",
            array
            (
                'is_oauth2_admin'      => $user->isOAuth2ServerAdmin(),
                'is_openstackid_admin' => $user->isOpenstackIdAdmin(),
                'group'                => $group,
                'non_selected_scopes'  => $non_selected_scopes,
            )
        );
    }

    // Resource servers
    public function listResourceServers() {
        $user             = $this->auth_service->getCurrentUser();
        $resource_servers = $this->resource_server_repository->getAll(1, PHP_INT_MAX);
        return View::make("oauth2.profile.admin.resource-servers",array(
            "is_oauth2_admin"      => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            'resource_servers'     => $resource_servers));
    }

    public function editResourceServer($id){
        $resource_server = $this->resource_server_repository->get($id);
        if(is_null($resource_server))
            return Response::view('errors.404', array(), 404);
        $user   = $this->auth_service->getCurrentUser();
        return View::make("oauth2.profile.admin.edit-resource-server",array(
            "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            'resource_server'=>$resource_server
        ));
    }

    public function editApi($id){
        $api = $this->api_repository->get($id);
        if(is_null($api))
            return Response::view('errors.404', array(), 404);
        $user   = $this->auth_service->getCurrentUser();
        return View::make("oauth2.profile.admin.edit-api",array(
            "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            'api'=>$api));
    }

    public function editScope($id){
        $scope = $this->scope_repository->get($id);
        if(is_null($scope))
            return Response::view('errors.404', array(), 404);
        $user   = $this->auth_service->getCurrentUser();
        return View::make("oauth2.profile.admin.edit-scope",array(
            "is_oauth2_admin" => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            'scope'=>$scope));
    }

    public function editEndpoint($id){
        $endpoint = $this->endpoint_repository->get($id);
        if(is_null($endpoint)) return Response::view('errors.404', array(), 404);
        $user   = $this->auth_service->getCurrentUser();
        $selected_scopes = array();
        $list = $endpoint->scopes()->get(array('id'));
        foreach($list as $selected_scope){
            array_push($selected_scopes,$selected_scope->id);
        }
        return View::make('oauth2.profile.admin.edit-endpoint',array(
            'is_oauth2_admin' => $user->isOAuth2ServerAdmin(),
            'is_openstackid_admin' => $user->isOpenstackIdAdmin(),
            'endpoint' => $endpoint ,
            'selected_scopes' => $selected_scopes));
    }

    public function editIssuedGrants(){

        $user           = $this->auth_service->getCurrentUser();
        $access_tokens  = $this->access_token_repository->getAllValidByUserId($user->getId(), 1, self::TokenPageSize);
        $refresh_tokens = $this->refresh_token_repository->getAllValidByUserId($user->getId(), 1, self::TokenPageSize);

        foreach($access_tokens->items() as $access_token){
            $friendly_scopes = $this->scope_service->getFriendlyScopesByName(explode(' ',$access_token->scope));
            $access_token->setFriendlyScopes(implode(', ',$friendly_scopes));
        }

        foreach($refresh_tokens->items() as $refresh_token){
            $friendly_scopes = $this->scope_service->getFriendlyScopesByName(explode(' ',$refresh_token->scope));
            $refresh_token->setFriendlyScopes(implode(', ',$friendly_scopes));
        }

        return View::make("oauth2.profile.edit-user-grants",
            array
            (
                'user_id'              => $user->getId(),
                'access_tokens'        => $access_tokens->items() ,
                'access_tokens_pages'  => $access_tokens->total() > 0 ? intval(ceil($access_tokens->total() / self::TokenPageSize)) : 0,
                'refresh_tokens'       => $refresh_tokens->items(),
                'refresh_tokens_pages' => $refresh_tokens->total() > 0 ? intval(ceil($refresh_tokens->total() / self::TokenPageSize)) : 0,
                'is_oauth2_admin'      => $user->isOAuth2ServerAdmin(),
                'is_openstackid_admin' => $user->isOpenstackIdAdmin(),
            )
        );
    }

    public function listOAuth2Clients(){
        $user    = $this->auth_service->getCurrentUser();
        $clients = $user->getClients();

        return View::make("oauth2.profile.clients", array(
            "username"             => $user->getFullName(),
            "user_id"              => $user->getId(),
            "is_oauth2_admin"      => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
            "use_system_scopes"    => $user->canUseSystemScopes(),
            'clients'              => $clients,
        ));
    }

    public function listLockedClients(){
        $user    = $this->auth_service->getCurrentUser();
        $clients = $this->client_repository->getAll(1, PHP_INT_MAX,[
            [
                'name'=>'locked',
                'op' => '=',
                'value'=> true
            ]
        ]);

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
        $users   = $this->user_repository->getAll(1, PHP_INT_MAX,[
            [
                'name'  => 'lock',
                'op'    => '=',
                'value' => true
            ]
        ]);

        return View::make('admin.users', [
                'username'             => $user->getFullName(),
                'user_id'              => $user->getId(),
                'is_oauth2_admin'      => $user->isOAuth2ServerAdmin(),
                'is_openstackid_admin' => $user->isOpenstackIdAdmin(),
                'users'                => $users,
        ]);
    }

    public function listServerConfig(){

        $user          = $this->auth_service->getCurrentUser();
        $config_values = array();
        $dictionary    = array
        (
            'MaxFailed.Login.Attempts',
            'MaxFailed.LoginAttempts.2ShowCaptcha',
            'OpenId.Private.Association.Lifetime',
            'OpenId.Session.Association.Lifetime',
            'OpenId.Nonce.Lifetime',
            'OAuth2.AuthorizationCode.Lifetime',
            'OAuth2.AccessToken.Lifetime',
            'OAuth2.IdToken.Lifetime',
            'OAuth2.RefreshToken.Lifetime',
            'OAuth2.AccessToken.Revoked.Lifetime',
            'OAuth2.AccessToken.Void.Lifetime',
            'OAuth2.RefreshToken.Revoked.Lifetime',
            'OAuth2SecurityPolicy.MaxBearerTokenDisclosureAttempts',
            'OAuth2SecurityPolicy.MinutesWithoutExceptions',
            'OAuth2SecurityPolicy.MaxInvalidClientExceptionAttempts',
            'OAuth2SecurityPolicy.MaxInvalidRedeemAuthCodeAttempts',
            'OAuth2SecurityPolicy.MaxInvalidClientCredentialsAttempts',
        );

        foreach($dictionary as $key)
            $config_values[$key] = $this->configuration_service->getConfigValue($key);

        return View::make("admin.server-config",
            array
            (
                "username"             => $user->getFullName(),
                "user_id"              => $user->getId(),
                "is_oauth2_admin"      => $user->isOAuth2ServerAdmin(),
                "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
                'config_values'        => $config_values,
            )
        );
    }

    public function saveServerConfig(){

        $values = Input::all();

        $rules = array
        (
            'general-max-failed-login-attempts'                                 => 'required|integer',
            'general-max-failed-login-attempts-captcha'                         => 'required|integer',
            'openid-private-association-lifetime'                               => 'required|integer',
            'openid-session-association-lifetime'                               => 'required|integer',
            'openid-nonce-lifetime'                                             => 'required|integer',
            'oauth2-auth-code-lifetime'                                         => 'required|integer',
            'oauth2-refresh-token-lifetime'                                     => 'required|integer',
            'oauth2-access-token-lifetime'                                      => 'required|integer',
            'oauth2-id-token-lifetime'                                          => 'required|integer',
            'oauth2-id-access-token-revoked-lifetime'                           => 'required|integer',
            'oauth2-id-access-token-void-lifetime'                              => 'required|integer',
            'oauth2-id-refresh-token-revoked-lifetime'                          => 'required|integer',
            'oauth2-id-security-policy-minutes-without-exceptions'              => 'required|integer',
            'oauth2-id-security-policy-max-bearer-token-disclosure-attempts'    => 'required|integer',
            'oauth2-id-security-policy-max-invalid-client-exception-attempts'   => 'required|integer',
            'oauth2-id-security-policy-max-invalid-redeem-auth-code-attempts'   => 'required|integer',
            'oauth2-id-security-policy-max-invalid-client-credentials-attempts' => 'required|integer',
        );

        $dictionary = array
        (
            'general-max-failed-login-attempts'                                 => 'MaxFailed.Login.Attempts',
            'general-max-failed-login-attempts-captcha'                         => 'MaxFailed.LoginAttempts.2ShowCaptcha',
            'openid-private-association-lifetime'                               => 'OpenId.Private.Association.Lifetime',
            'openid-session-association-lifetime'                               => 'OpenId.Session.Association.Lifetime',
            'openid-nonce-lifetime'                                             => 'OpenId.Nonce.Lifetime',
            'oauth2-auth-code-lifetime'                                         => 'OAuth2.AuthorizationCode.Lifetime',
            'oauth2-access-token-lifetime'                                      => 'OAuth2.AccessToken.Lifetime',
            'oauth2-id-token-lifetime'                                          => 'OAuth2.IdToken.Lifetime',
            'oauth2-refresh-token-lifetime'                                     => 'OAuth2.RefreshToken.Lifetime',
            'oauth2-id-access-token-revoked-lifetime'                           => 'OAuth2.AccessToken.Revoked.Lifetime',
            'oauth2-id-access-token-void-lifetime'                              => 'OAuth2.AccessToken.Void.Lifetime',
            'oauth2-id-refresh-token-revoked-lifetime'                          => 'OAuth2.RefreshToken.Revoked.Lifetime',
            'oauth2-id-security-policy-minutes-without-exceptions'              => 'OAuth2SecurityPolicy.MinutesWithoutExceptions',
            'oauth2-id-security-policy-max-bearer-token-disclosure-attempts'    => 'OAuth2SecurityPolicy.MaxBearerTokenDisclosureAttempts',
            'oauth2-id-security-policy-max-invalid-client-exception-attempts'   => 'OAuth2SecurityPolicy.MaxInvalidClientExceptionAttempts',
            'oauth2-id-security-policy-max-invalid-redeem-auth-code-attempts'   => 'OAuth2SecurityPolicy.MaxInvalidRedeemAuthCodeAttempts',
            'oauth2-id-security-policy-max-invalid-client-credentials-attempts' => 'OAuth2SecurityPolicy.MaxInvalidClientCredentialsAttempts',
        );

        // Creates a Validator instance and validates the data.
        $validation = Validator::make($values, $rules);

        if ($validation->fails())
        {
            return Redirect::action("AdminController@listServerConfig")->withErrors($validation);
        }

        foreach($values as $field => $value)
        {
            if(array_key_exists($field, $dictionary))
                $this->configuration_service->saveConfigValue($dictionary[$field], $value);
        }

        return Redirect::action("AdminController@listServerConfig");
    }

    public function listBannedIPs(){
        $user    = $this->auth_service->getCurrentUser();
        $ips     = $this->banned_ips_service->getByPage(1, PHP_INT_MAX);
        return View::make("admin.banned-ips",
            array
            (
                "username"             => $user->getFullName(),
                "user_id"              => $user->getId(),
                "is_oauth2_admin"      => $user->isOAuth2ServerAdmin(),
                "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
                "ips"                  => $ips
            )
        );
    }

    public function listServerPrivateKeys(){

        $user = $this->auth_service->getCurrentUser();

        return View::make("oauth2.profile.admin.server-private-keys", array(
            'private_keys'         => $this->private_keys_repository->getAll(1, PHP_INT_MAX),
            "is_oauth2_admin"      => $user->isOAuth2ServerAdmin(),
            "is_openstackid_admin" => $user->isOpenstackIdAdmin(),
        ));
    }
}