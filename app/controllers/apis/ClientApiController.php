<?php

use oauth2\exceptions\AllowedClientUriAlreadyExistsException;
use oauth2\exceptions\InvalidClientException;
use oauth2\services\IApiScopeService;
use oauth2\services\IClientService;
use oauth2\services\ITokenService;
use utils\services\ILogService;

/**
 * Class ClientApiController
 * Client REST API
 */
class ClientApiController extends AbstractRESTController implements IRESTController
{

    private $client_service;
    private $scope_service;
    private $token_service;

    public function __construct(IApiScopeService $scope_service, ITokenService $token_service, IClientService $client_service, ILogService $log_service)
    {
        parent::__construct($log_service);

        $this->client_service = $client_service;
        $this->scope_service  = $scope_service;
        $this->token_service  = $token_service;

        //set filters allowed values
        $this->allowed_filter_fields = array('user_id');
        $this->allowed_filter_op     = array('user_id' => array('='));
        $this->allowed_filter_value  = array('user_id' => '/^\d+$/');
    }

    /**
     * Deletes an existing client
     * @param $id client id
     * @return mixed
     */
    public function delete($id)
    {
        try {
            $res = $this->client_service->deleteClientByIdentifier($id);
            return $res ? Response::json('ok', 200) : $this->error404(array('error' => 'operation failed'));
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
                'app_desc' => 'required|text',
                'app_type' => 'required|integer|applicationtype',
            );

            // Create a new validator instance.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error'=>'validation','messages' => $messages));
            }


            if ($this->client_service->existClientAppName($values['app_name'])) {
                return $this->error400(array('error' => 'application Name already exists!.'));
            }

            $new_client = $this->client_service->addClient(intval($values['app_type']), intval($values['user_id']), trim($values['app_name']), trim($values['app_desc']));

            return $this->ok(array('client_id' => $new_client->id));

        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
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

    public function getByPage($page_nbr, $page_size)
    {
        try {
            //check for optional filters param on querystring
            $filters = Input::get('filters',null);
            $list = $this->client_service->getAll($page_nbr, $page_size,$this->getFilters($filters));
            $items = array();
            foreach ($list->getItems() as $client) {
                array_push($items, $client->toArray());
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

    public function update()
    {
        try {

            $values = Input::all();

            $rules = array(
                'id' => 'required|integer',
                'app_name' => 'sometimes|required|alpha_dash|max:255',
                'app_description' => 'sometimes|required|text',
                'active' => 'sometimes|required|boolean',
                'locked' => 'sometimes|required|boolean',
                'use_refresh_token' => 'sometimes|required|boolean',
                'rotate_refresh_token' => 'sometimes|required|boolean',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error'=>'validation','messages' => $messages));
            }

            $res = $this->client_service->update(intval($values['id']), $values);

            return $res ? Response::json('ok', 200) : $this->error400(array('error' => 'operation failed'));

        } catch (InvalidClientException $ex1) {
            $this->log_service->error($ex1);
            return $this->error404(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function getRegisteredUris($id)
    {
        try {
            $client = $this->client_service->getClientByIdentifier($id);
            $allowed_uris = $client->authorized_uris()->get(array('id', 'uri'));

            $data = array();
            foreach ($allowed_uris as $uri) {
                array_push($data, $uri->toArray());
            }

            return $this->ok(array('allowed_uris' => $data));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function addAllowedRedirectUri($id)
    {
        try {
            $values = Input::All();
            // Build the validation constraint set.
            $rules = array(
                'redirect_uri' => 'sslurl|required',
            );
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);
            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error' => $messages));
            }
            $res = $this->client_service->addClientAllowedUri($id, $values['redirect_uri']);
            return $res ? Response::json('ok', 200) : $this->error404(array('error' => 'operation failed'));
        } catch (AllowedClientUriAlreadyExistsException $ex1) {
            $this->log_service->error($ex1);
            return $this->error400(array('error' => $ex1->getMessage()));
        } catch (InvalidClientException $ex2) {
            $this->log_service->error($ex2);
            return $this->error400(array('error' => $ex2->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function deleteClientAllowedUri($id, $uri_id)
    {
        try {
            $res = $this->client_service->deleteClientAllowedUri($id, $uri_id);
            return $res ? Response::json('ok', 200) : $this->error404(array('error' => 'operation failed'));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function addAllowedScope($id)
    {
        try {
            $values = Input::All();
            // Build the validation constraint set.
            $rules = array(
                'scope_id' => 'required|integer',
                'checked' => 'required|boolean',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);
            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();
                return $this->error400(array('error' => $messages));
            }

            $checked = $values['checked'];
            $scope_id = $values['scope_id'];
            $res = $checked ? $this->client_service->addClientScope($id, $scope_id) : $this->client_service->deleteClientScope($id, $scope_id);
            return Response::json('ok', 200);
        } catch (InvalidClientException $ex1) {
            $this->log_service->error($ex1);
            return $this->error400(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function updateStatus($id,$active)
    {
        try {

            $res = $this->client_service->activateClient($id, $active);

            return $res ? Response::json('ok', 200) : $this->error404(array('error' => 'operation failed'));

        } catch (InvalidClientException $ex1) {
            $this->log_service->error($ex1);
            return $this->error400(array('error' => $ex1->getMessage()));
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function regenerateClientSecret($id)
    {
        try {
            $res = $this->client_service->regenerateClientSecret($id);
            return !empty($res) ? Response::json(array('new_secret' => $res), 200) : $this->error404(array('error' => 'operation failed'));
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
                return $this->error400(array('error' => $messages));
            }

            $res = $this->client_service->setRefreshTokenUsage($id, $values['use_refresh_token']);

            return $res ? Response::json('ok', 200) : $this->error404(array('error' => 'operation failed'));

        } catch (InvalidClientException $ex1) {
            $this->log_service->error($ex1);
            return $this->error400(array('error' => $ex1->getMessage()));
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
                return $this->error400(array('error' => $messages));
            }

            $res = $this->client_service->setRotateRefreshTokenPolicy($id, $values['rotate_refresh_token']);
            return $res ? Response::json('ok', 200) : $this->error404(array('error' => 'operation failed'));

        } catch (InvalidClientException $ex1) {
            $this->log_service->error($ex1);
            return $this->error400(array('error' => $ex1->getMessage()));
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
                case 'access-token':
                {
                    $token = $this->token_service->getAccessToken($value,true);
                    if (is_null($token)) {
                        return $this->error404(array('error' => sprintf('access token %s does not exists!', $value)));
                    }
                    if ($token->getClientId() !== $client->client_id) {
                        return $this->error404(array('error' => sprintf('access token %s does not belongs to client id !', $value, $id)));
                    }
                    $res = $this->token_service->revokeAccessToken($value, true);
                }
                    break;
                case 'refresh-token':
                {
                    $token = $this->token_service->getRefreshToken($value,true);
                    if (is_null($token)) {
                        return $this->error404(array('error' => sprintf('refresh token %s does not exists!', $value)));
                    }
                    if ($token->getClientId() !== $client->client_id) {
                        return $this->error404(array('error' => sprintf('refresh token %s does not belongs to client id !', $value, $id)));
                    }
                    $res = $this->token_service->revokeRefreshToken($value, true);
                }
                    break;
                default:
                    break;
            }

            return $res ? Response::json('ok', 200) : $this->error404(array('error' => 'operation failed'));
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

}