<?php namespace App\Http\Controllers\Api;

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

use App\Http\Controllers\ICRUDController;
use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use OAuth2\Exceptions\ExpiredAccessTokenException;
use OAuth2\Exceptions\InvalidApiScope;
use OAuth2\Repositories\IAccessTokenRepository;
use OAuth2\Repositories\IClientRepository;
use OAuth2\Repositories\IRefreshTokenRepository;
use OAuth2\Services\ITokenService;
use OAuth2\Services\IApiScopeService;
use OAuth2\Services\IClientService;
use Utils\Exceptions\EntityNotFoundException;
use Utils\Services\IAuthService;
use Utils\Services\ILogService;
use Services\Exceptions\ValidationException;
use Illuminate\Support\Facades\Log;

/**
 * Class ClientApiController
 * @package App\Http\Controllers\Api
 */
final class ClientApiController extends AbstractRESTController implements ICRUDController
{

    /**
     * @var IClientService
     */
    private $client_service;

    /**
     * @var IApiScopeService
     */
    private $scope_service;

    /**
     * @var ITokenService
     */
    private $token_service;

    /**
     * @var IAuthService
     */
    private $auth_service;

    /**
     * @var IClientRepository
     */
    private $client_repository;

    /**
     * @var IAccessTokenRepository
     */
    private $access_token_repository;

    /**
     * @var IRefreshTokenRepository
     */
    private $refresh_token_repository;


    /**
     * ClientApiController constructor.
     * @param IApiScopeService $scope_service
     * @param ITokenService $token_service
     * @param IClientService $client_service
     * @param IAuthService $auth_service
     * @param ILogService $log_service
     * @param IClientRepository $client_repository
     * @param IAccessTokenRepository $access_token_repository
     * @param IRefreshTokenRepository $refresh_token_repository
     */
    public function __construct
    (
        IApiScopeService  $scope_service,
        ITokenService     $token_service,
        IClientService    $client_service,
        IAuthService      $auth_service,
        ILogService       $log_service,
        IClientRepository $client_repository,
        IAccessTokenRepository $access_token_repository,
        IRefreshTokenRepository $refresh_token_repository
    )
    {
        parent::__construct($log_service);

        $this->client_service            = $client_service;
        $this->scope_service             = $scope_service;
        $this->token_service             = $token_service;
        $this->auth_service              = $auth_service;
        $this->access_token_repository   = $access_token_repository;
        $this->refresh_token_repository  = $refresh_token_repository;
        $this->client_repository         = $client_repository;

        //set filters allowed values
        $this->allowed_filter_fields     = ['user_id'];
        $this->allowed_projection_fields = ['*'];
    }

    public function get($id)
    {
        try {
            $client = $this->client_repository->getClientByIdentifier($id);
            if (is_null($client))
            {
                return $this->error404(array('error' => 'client not found'));
            }
            return $this->ok($client->toArray());
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * Deletes an existing client
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        try {
            $this->client_service->deleteClientByIdentifier($id);
            return $this->deleted();
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * Creates an existing client
     * @return mixed
     */
    public function create()
    {
        try
        {

            $values = Input::All();

            // Build the validation constraint set.
            $rules = array
            (
                'app_name'         => 'required|freetext|max:255',
                'app_description'  => 'required|freetext|max:512',
                'application_type' => 'required|applicationtype',
                'website'          => 'sometimes|required|url',
                'admin_users'      => 'sometimes|required|user_ids',
            );

            // Create a new validator instance.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412(array('error' => 'validation', 'messages' => $messages));
            }


            $admin_users = isset($values['admin_users']) ? trim($values['admin_users']): null;
            $admin_users = empty($admin_users) ? array() : explode(',',$admin_users);
            $website     = isset($values['website']) ? trim($values['website']): null;

            $new_client = $this->client_service->register
            (
                $values['application_type'],
                trim($values['app_name']),
                trim($values['app_description']),
                $website,
                $admin_users
            );

            return $this->created
            (
                array
                (
                    'id'            => $new_client->id,
                    'client_id'     => $new_client->client_id,
                    'client_secret' => $new_client->client_secret,
                )
            );

        }
        catch(ValidationException $ex2)
        {
            $this->log_service->error($ex2);
            return $this->error412(array($ex2->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function update()
    {
        try {

            $values = Input::all();

            $rules = array(
                'id'                              => 'required|integer',
                //'application_type'                => 'required|application_type',
                'app_name'                        => 'sometimes|required|freetext|max:255',
                'app_description'                 => 'sometimes|required|freetext|max:512',
                'website'                         => 'sometimes|required|url',
                'active'                          => 'sometimes|required|boolean',
                'locked'                          => 'sometimes|required|boolean',
                'use_refresh_token'               => 'sometimes|required|boolean',
                'rotate_refresh_token'            => 'sometimes|required|boolean',
                'contacts'                        => 'sometimes|required|email_set',
                'logo_uri'                        => 'sometimes|required|url',
                'tos_uri'                         => 'sometimes|required|url',
                'redirect_uris'                   => 'sometimes|required|custom_url_set:application_type',
                'post_logout_redirect_uris'       => 'sometimes|required|ssl_url_set',
                'allowed_origins'                 => 'sometimes|required|ssl_url_set',
                'logout_uri'                      => 'sometimes|required|url',
                'logout_session_required'         => 'sometimes|required|boolean',
                'logout_use_iframe'               => 'sometimes|required|boolean',
                'policy_uri'                      => 'sometimes|required|url',
                'jwks_uri'                        => 'sometimes|required|url',
                'default_max_age'                 => 'sometimes|required|integer',
                'logout_use_iframe'               => 'sometimes|required|boolean',
                'require_auth_time'               => 'sometimes|required|boolean',
                'token_endpoint_auth_method'      => 'sometimes|required|token_endpoint_auth_method',
                'token_endpoint_auth_signing_alg' => 'sometimes|required|signing_alg',
                'subject_type'                    => 'sometimes|required|subject_type',
                'userinfo_signed_response_alg'    => 'sometimes|required|signing_alg',
                'userinfo_encrypted_response_alg' => 'sometimes|required|encrypted_alg',
                'userinfo_encrypted_response_enc' => 'sometimes|required|encrypted_enc',
                'id_token_signed_response_alg'    => 'sometimes|required|signing_alg',
                'id_token_encrypted_response_alg' => 'sometimes|required|encrypted_alg',
                'id_token_encrypted_response_enc' => 'sometimes|required|encrypted_enc',
                'admin_users'                     => 'sometimes|required|user_ids',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412(array('error' => 'validation', 'messages' => $messages));
            }

            $this->client_service->update(intval($values['id']), $values);

            return $this->ok();

        }
        catch (EntityNotFoundException $ex1)
        {
            $this->log_service->error($ex1);
            return $this->error404(array('error' => $ex1->getMessage()));
        }
        catch(ValidationException $ex2)
        {
            $this->log_service->error($ex2);
            return $this->error412(array($ex2->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function getByPage()
    {
        try {

            $items   = array();
            $user    = $this->auth_service->getCurrentUser();
            if(is_null($user)) return $this->error403();
            $clients = $user->getClients();

            foreach ($clients as $client)
            {
                $data = $client->toArray();
                $data['application_type'] = $client->getFriendlyApplicationType();
                $data['is_own']           = $client->isOwner($this->auth_service->getCurrentUser());
                $data['modified_by']      = $client->getEditedByNice();
                array_push($items, $data);
            }

            return $this->ok
            (
                array
                (
                    'page'        => $items,
                    'total_items' => count($items)
                )
            );

        }
        catch (Exception $ex)
        {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @param $scope_id
     * @return mixed
     */
    public function addAllowedScope($id, $scope_id)
    {
        try
        {
            $this->client_service->addClientScope($id, $scope_id);
            return $this->ok();
        }
        catch (EntityNotFoundException $ex1)
        {
            $this->log_service->error($ex1);
            return $this->error404(array('error' => $ex1->getMessage()));
        }
        catch (InvalidApiScope $ex2)
        {
            $this->log_service->error($ex2);
            return $this->error412(array('messages' => $ex2->getMessage()));
        }
        catch (Exception $ex)
        {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @param $scope_id
     * @return mixed
     */
    public function removeAllowedScope($id, $scope_id)
    {
        try
        {
            $this->client_service->deleteClientScope($id, $scope_id);
            return $this->ok();
        } catch (EntityNotFoundException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function activate($id)
    {
        try {
            $this->client_service->activateClient($id, true);
            return $this->ok();
        } catch (EntityNotFoundException $ex1) {
            $this->log_service->error($ex1);
            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function deactivate($id)
    {
        try {
            $this->client_service->activateClient($id, false);
            return $this->ok();
        } catch (EntityNotFoundException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function regenerateClientSecret($id)
    {
        try
        {
            $client = $this->client_service->regenerateClientSecret($id);

            return !is_null($client) ?
                $this->ok
                (
                    array
                    (
                        'new_secret'          => $client->getClientSecret(),
                        'new_expiration_date' => $client->getClientSecretExpiration(),
                    )
                ) : $this->error404(array('error' => 'operation failed'));
        }
        catch (Exception $ex)
        {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function setRefreshTokenClient($id)
    {
        try {
            $values = Input::All();

            // Build the validation constraint set.
            $rules = array(
                'use_refresh_token' => 'required|boolean'
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);
            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error400(array('error' => 'validation', 'messages' => $messages));
            }

            $this->client_service->setRefreshTokenUsage($id, $values['use_refresh_token']);

            return $this->ok();

        } catch (EntityNotFoundException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function setRotateRefreshTokenPolicy($id)
    {
        try {
            $values = Input::All();

            // Build the validation constraint set.
            $rules = array(
                'rotate_refresh_token' => 'required|boolean'
            );
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);
            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error400(array('error' => 'validation', 'messages' => $messages));
            }

            $this->client_service->setRotateRefreshTokenPolicy($id, $values['rotate_refresh_token']);

            return $this->ok();

        } catch (EntityNotFoundException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @param $value
     * @param $hint
     * @return mixed
     */
    public function revokeToken($id, $value, $hint)
    {
        try {
            $res = false;
            $client = $this->client_repository->getClientByIdentifier($id);
            switch ($hint) {
                case 'access-token': {
                    $token = $this->token_service->getAccessToken($value, true);
                    if (is_null($token)) {
                        return $this->error404(array('error' => sprintf('access token %s does not exists!', $value)));
                    }
                    Log::debug(sprintf('access token client id %s - client id %s ',$token->getClientId() , $client->client_id));
                    if ($token->getClientId() !== $client->client_id) {
                        return $this->error412(array(
                            'error' => sprintf('access token %s does not belongs to client id !', $value, $id)
                        ));
                    }
                    $res = $this->token_service->revokeAccessToken($value, true);
                }
                    break;
                case 'refresh-token': {
                    $token = $this->token_service->getRefreshToken($value, true);
                    if (is_null($token)) {
                        return $this->error404(array('error' => sprintf('refresh token %s does not exists!', $value)));
                    }
                    Log::debug(sprintf('refresh token client id %s - client id %s ',$token->getClientId() , $client->client_id));
                    if ($token->getClientId() !== $client->client_id) {
                        return $this->error412(array(
                            'error' => sprintf('refresh token %s does not belongs to client id !', $value, $id)
                        ));
                    }
                    $res = $this->token_service->revokeRefreshToken($value, true);
                }
                    break;
                default:
                    break;
            }

            return $res ? $this->ok() : $this->error404(array('error' => 'operation failed'));
        }
        catch(ExpiredAccessTokenException $ex1){
            $this->log_service->warning($ex1);
            return $this->error404();
        }
        catch(Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getAccessTokens($id)
    {
        try {
            $page_nbr  = intval(Input::get('offset', 1));
            $page_size = intval(Input::get('limit', 10));

            $client    = $this->client_repository->getClientByIdentifier($id);

            if(is_null($client))
                throw new EntityNotFoundException();

            $paginator = $this->access_token_repository->getAllValidByClientIdentifier($id, $page_nbr, $page_size);
            $res       = [];

            foreach ($paginator->items() as $token) {
                $res[] =  [
                    'value'    => $token->value,
                    'scope'    => $token->scope,
                    'lifetime' => $token->getRemainingLifetime(),
                    'issued'   => $token->created_at->format('Y-m-d H:i:s')
                ];
            }
            return $this->ok([
                'total' => $paginator->total(),
                'pages' => $paginator->total() > 0  && $page_size > 0 ? ceil($paginator->total()/$page_size) : 0,
                'items' => $res
            ]);
        }
        catch (EntityNotFoundException $ex1) {
            $this->log_service->warning($ex1);
            return $this->error404();
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getRefreshTokens($id)
    {
        try {
            $page_nbr  = intval(Input::get('offset', 1));
            $page_size = intval(Input::get('limit', 10));

            $client    = $this->client_repository->getClientByIdentifier($id);

            if(is_null($client))
                throw new EntityNotFoundException();

            $paginator = $this->refresh_token_repository->getAllValidByClientIdentifier($id, $page_nbr, $page_size);
            $res       = [];

            foreach ($paginator->items() as $token) {
                $res[] = [
                    'value'    => $token->value,
                    'scope'    => $token->scope,
                    'lifetime' => $token->getRemainingLifetime(),
                    'issued'   => $token->created_at->format('Y-m-d H:i:s')
                ];
            }

            return $this->ok([
                'total' => $paginator->total(),
                'pages' => $paginator->total() > 0 ? ceil($paginator->total()/$page_size) : 0,
                'items' => $res
            ]);
        }
        catch (EntityNotFoundException $ex1) {
            $this->log_service->warning($ex1);
            return $this->error404();
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function getAccessTokensByCurrentUser()
    {
        try {
            $user      = $this->auth_service->getCurrentUser();
            $page_nbr  = intval(Input::get('offset', 1));
            $page_size = intval(Input::get('limit', 10));


            $paginator = $this->access_token_repository->getAllValidByUserId($user->getId(), $page_nbr, $page_size);
            $res       = [];

            foreach ($paginator->items() as $token) {
                $res[] =  [
                    'app_type' => $token->client()->first()->getFriendlyApplicationType(),
                    'value'    => $token->value,
                    'scope'    => $token->scope,
                    'lifetime' => $token->getRemainingLifetime(),
                    'issued'   => $token->created_at->format('Y-m-d H:i:s')
                ];
            }
            return $this->ok([
                'total' => $paginator->total(),
                'pages' => $paginator->total() > 0 ? ceil($paginator->total()/$page_size) : 0,
                'items' => $res
            ]);
        }
        catch (EntityNotFoundException $ex1) {
            $this->log_service->warning($ex1);
            return $this->error404();
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function getRefreshTokensByCurrentUser()
    {
        try {
            $user      = $this->auth_service->getCurrentUser();
            $page_nbr  = intval(Input::get('offset', 1));
            $page_size = intval(Input::get('limit', 10));

            $paginator = $this->refresh_token_repository->getAllValidByUserId($user->getId(), $page_nbr, $page_size);
            $res       = [];

            foreach ($paginator->items() as $token) {
                $res[] = [
                    'app_type' => $token->client()->first()->getFriendlyApplicationType(),
                    'value'    => $token->value,
                    'scope'    => $token->scope,
                    'lifetime' => $token->getRemainingLifetime(),
                    'issued'   => $token->created_at->format('Y-m-d H:i:s')
                ];
            }

            return $this->ok([
                'total' => $paginator->total(),
                'pages' => $paginator->total() > 0 ? ceil($paginator->total()/$page_size) : 0,
                'items' => $res
            ]);
        }
        catch (EntityNotFoundException $ex1) {
            $this->log_service->warning($ex1);
            return $this->error404();
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function unlock($id)
    {
        try {
            $this->client_service->unlockClient($id);
            return $this->ok();

        } catch (EntityNotFoundException $ex1) {

            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

}