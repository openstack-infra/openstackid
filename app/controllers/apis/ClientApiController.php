<?php

use oauth2\exceptions\AbsentClientException;
use oauth2\exceptions\AllowedClientUriAlreadyExistsException;
use oauth2\services\IApiScopeService;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use utils\services\ILogService;

/**
 * Class ClientApiController
 * Client REST API
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
     * @param IApiScopeService $scope_service
     * @param ITokenService $token_service
     * @param IClientService $client_service
     * @param ILogService $log_service
     */
    public function __construct(
        IApiScopeService $scope_service,
        ITokenService $token_service,
        IClientService $client_service,
        ILogService $log_service
    ) {
        parent::__construct($log_service);

        $this->client_service = $client_service;
        $this->scope_service = $scope_service;
        $this->token_service = $token_service;

        //set filters allowed values
        $this->allowed_filter_fields = array('user_id');
        $this->allowed_projection_fields = array('*');
    }

    public function get($id)
    {
        try {
            $client = $this->client_service->get($id);
            if (is_null($client)) {
                return $this->error404(array('error' => 'client not found'));
            }
            $data = $client->toArray();

            return $this->ok($data);
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
            $res = $this->client_service->deleteClientByIdentifier($id);

            return $res ? $this->deleted() : $this->error404(array('error' => 'operation failed'));
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
        try {
            $values = Input::All();

            // Build the validation constraint set.
            $rules = array(
                'user_id' => 'required|integer',
                'app_name' => 'required|alpha_dash|max:255',
                'app_description' => 'required|freetext',
                'website' => 'url',
                'application_type' => 'required|applicationtype',
            );

            // Create a new validator instance.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error400(array('error' => 'validation', 'messages' => $messages));
            }

            if ($this->client_service->existClientAppName($values['app_name'])) {
                return $this->error400(array('error' => 'application Name already exists!.'));
            }

            $new_client = $this->client_service->addClient($values['application_type'], intval($values['user_id']),
                trim($values['app_name']), trim($values['app_description']), trim($values['website']));

            return $this->created(array('client_id' => $new_client->id));

        } catch (Exception $ex) {
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
            //check for optional filters param on querystring
            $fields = $this->getProjection(Input::get('fields', null));
            $filters = $this->getFilters(Input::except('fields', 'limit', 'offset'));
            $page_nbr = intval(Input::get('offset', 1));
            $page_size = intval(Input::get('limit', 10));

            $list = $this->client_service->getAll($page_nbr, $page_size, $filters, $fields);
            $items = array();
            foreach ($list->getItems() as $client) {
                $data = $client->toArray();
                $data['application_type'] = $client->getFriendlyApplicationType();
                array_push($items, $data);
            }

            return $this->ok(array(
                'page' => $items,
                'total_items' => $list->getTotal()
            ));
        } catch (Exception $ex) {
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
                'id' => 'required|integer',
                'application_type' =>'required|application_type',
                'app_name' => 'sometimes|required|alpha_dash|max:255',
                'app_description' => 'sometimes|required|freetext',
                'website' => 'url',
                'active' => 'sometimes|required|boolean',
                'locked' => 'sometimes|required|boolean',
                'use_refresh_token' => 'sometimes|required|boolean',
                'rotate_refresh_token' => 'sometimes|required|boolean',
                'contacts' => 'email_set',
                'logo_uri' => 'url',
                'tos_uri' => 'url',
                'redirect_uris' => 'custom_url_set:application_type',
                'post_logout_redirect_uris' => 'ssl_url_set',
                'allowed_origins' => 'ssl_url_set',
                'logout_uri' => 'url',
                'logout_session_required' => 'sometimes|required|boolean',
                'logout_use_iframe' => 'sometimes|required|boolean',
                'policy_uri' => 'url',
                'jwks_uri' => 'url',
                'default_max_age' => 'sometimes|required|integer',
                'logout_use_iframe' => 'sometimes|required|boolean',
                'require_auth_time' => 'sometimes|required|boolean',
                'token_endpoint_auth_method' => 'sometimes|required|token_endpoint_auth_method',
                'token_endpoint_auth_signing_alg' => 'sometimes|required|signing_alg',
                'subject_type' => 'sometimes|required|subject_type',
                'userinfo_signed_response_alg' => 'sometimes|required|signing_alg',
                'userinfo_encrypted_response_alg' => 'sometimes|required|encrypted_alg',
                'userinfo_encrypted_response_enc' => 'sometimes|required|encrypted_enc',
                'id_token_signed_response_alg' => 'sometimes|required|signing_alg',
                'id_token_encrypted_response_alg' => 'sometimes|required|encrypted_alg',
                'id_token_encrypted_response_enc' => 'sometimes|required|encrypted_enc',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error400(array('error' => 'validation', 'messages' => $messages));
            }

            $res = $this->client_service->update(intval($values['id']), $values);

            return $res ? $this->ok() : $this->error400(array('error' => 'operation failed'));

        } catch (AbsentClientException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }


    public function addAllowedScope($id, $scope_id)
    {
        try {
            $this->client_service->addClientScope($id, $scope_id);

            return $this->ok();
        } catch (AbsentClientException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    public function removeAllowedScope($id, $scope_id)
    {
        try {
            $res = $this->client_service->deleteClientScope($id, $scope_id);

            return $res ? $this->ok() : $this->error404(array('error' => 'operation failed'));
        } catch (AbsentClientException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }


    public function activate($id)
    {
        try {
            $res = $this->client_service->activateClient($id, true);

            return $res ? $this->ok() : $this->error404(array('error' => 'operation failed'));
        } catch (AbsentClientException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    public function deactivate($id)
    {
        try {
            $res = $this->client_service->activateClient($id, false);

            return $res ? $this->ok() : $this->error404(array('error' => 'operation failed'));
        } catch (AbsentClientException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }


    public function regenerateClientSecret($id)
    {
        try {
            $res = $this->client_service->regenerateClientSecret($id);

            return !empty($res) ?
                $this->ok(array('new_secret' => $res)) : $this->error404(array('error' => 'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

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

            $res = $this->client_service->setRefreshTokenUsage($id, $values['use_refresh_token']);

            return $res ? $this->ok() : $this->error404(array('error' => 'operation failed'));

        } catch (AbsentClientException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

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

            $res = $this->client_service->setRotateRefreshTokenPolicy($id, $values['rotate_refresh_token']);

            return $res ? $this->ok() : $this->error404(array('error' => 'operation failed'));
        } catch (AbsentClientException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    public function revokeToken($id, $value, $hint)
    {
        try {
            $res = false;
            $client = $this->client_service->getClientByIdentifier($id);
            switch ($hint) {
                case 'access-token': {
                    $token = $this->token_service->getAccessToken($value, true);
                    if (is_null($token)) {
                        return $this->error404(array('error' => sprintf('access token %s does not exists!', $value)));
                    }
                    if ($token->getClientId() !== $client->client_id) {
                        return $this->error404(array(
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
                    if ($token->getClientId() !== $client->client_id) {
                        return $this->error404(array(
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
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    public function getAccessTokens($id)
    {
        try {
            $client = $this->client_service->getClientByIdentifier($id);
            $access_tokens = $this->token_service->getAccessTokenByClient($client->client_id);
            $res = array();
            foreach ($access_tokens as $token) {
                $friendly_scopes = $this->scope_service->getFriendlyScopesByName(explode(' ', $token->scope));
                array_push($res, array(
                    'value' => $token->value,
                    'scope' => implode(',', $friendly_scopes),
                    'lifetime' => $token->getRemainingLifetime(),
                    'issued' => $token->created_at->format('Y-m-d H:i:s')
                ));
            }

            return $this->ok(array('access_tokens' => $res));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

    public function getRefreshTokens($id)
    {
        try {
            $client = $this->client_service->getClientByIdentifier($id);
            $refresh_tokens = $this->token_service->getRefreshTokenByClient($client->client_id);
            $res = array();
            foreach ($refresh_tokens as $token) {
                $friendly_scopes = $this->scope_service->getFriendlyScopesByName(explode(' ', $token->scope));
                array_push($res, array(
                    'value' => $token->value,
                    'scope' => implode(',', $friendly_scopes),
                    'lifetime' => $token->getRemainingLifetime(),
                    'issued' => $token->created_at->format('Y-m-d H:i:s')
                ));
            }

            return $this->ok(array('refresh_tokens' => $res));
        } catch (Exception $ex) {
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
            $res = $this->client_service->unlockClient($id);

            return $res ? $this->ok() : $this->error404(array('error' => 'operation failed'));
        } catch (AbsentClientException $ex1) {
            $this->log_service->error($ex1);

            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);

            return $this->error500($ex);
        }
    }

}