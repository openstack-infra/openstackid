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
use Auth\Repositories\IUserRepository;
use Exception;
use Illuminate\Support\Facades\Input;
use OAuth2\Exceptions\ExpiredAccessTokenException;
use OAuth2\Services\ITokenService;
use OpenId\Services\IUserService;
use Utils\Services\ILogService;

/**
 * Class UserApiController
 * @package App\Http\Controllers\Api
 */
class UserApiController extends AbstractRESTController implements ICRUDController {

    /**
     * @var IUserService
     */
    private $user_service;
    /**
     * @var ITokenService
     */
    private $token_service;

    /**
     * @var IUserRepository
     */
    private $user_repository;

    /**
     * UserApiController constructor.
     * @param IUserRepository $user_repository
     * @param ILogService $log_service
     * @param IUserService $user_service
     * @param ITokenService $token_service
     */
    public function __construct
    (
        IUserRepository $user_repository,
        ILogService $log_service,
        IUserService $user_service,
        ITokenService $token_service
    ){
        parent::__construct($log_service);

        $this->user_service    = $user_service;
        $this->token_service   = $token_service;
        $this->user_repository = $user_repository;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function unlock($id){
        try {
            $this->user_service->unlockUser($id);
            return $this->updated();
        }
        catch (AbsentClientException $ex1) {
            $this->log_service->error($ex1);
            return $this->error404(array('error' => $ex1->getMessage()));
        }
        catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $id
     * @param $value
     * @return mixed
     */
    public function revokeToken($id,$value){

        try{
            $hint = Input::get('hint','none');

            switch($hint){
                case 'access_token':{
                    $token = $this->token_service->getAccessToken($value,true);
                    if(is_null($token))
                        throw new Exception(sprintf("access token %s expired!.",$value));
                    if(is_null($token->getUserId()) || intval($token->getUserId())!=intval($id))
                        throw new Exception(sprintf("access token %s does not belongs to user id %s!.",$value,$id));
                    $this->token_service->revokeAccessToken($value,true);
                }
                    break;
                case 'refresh_token':
                    $token = $this->token_service->getRefreshToken($value,true);
                    if(is_null($token))
                        throw new Exception(sprintf("access token %s expired!.",$value));
                    if(is_null($token->getUserId()) || intval($token->getUserId())!=intval($id))
                        throw new Exception(sprintf("refresh token %s does not belongs to user id %s!.",$value,$id));
                    $this->token_service->revokeRefreshToken($value,true);
                    break;
                default:
                    throw new Exception(sprintf("hint %s not allowed",$hint));
                    break;
            }
            return $this->ok();
        }
        catch(ExpiredAccessTokenException $ex1){
            $this->log_service->warning($ex1);
            return $this->error404();
        }
        catch(Exception $ex){
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }


    public function get($id)
    {
        try {
            $user       = $this->user_service->get($id);
            if(is_null($user)){
                return $this->error404(array('error' => 'user not found'));
            }
            $data = $user->toArray();
            return $this->ok($data);
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function getByPage()
    {
        // TODO: Implement getByPage() method.
    }

    public function delete($id)
    {
        // TODO: Implement delete() method.
    }

    public function update()
    {
        // TODO: Implement update() method.
    }

    public function fetch()
    {
        $values = Input::all();
        if(!isset($values['t'])) return $this->error404();

        $term  = $values['t'];
        $users = $this->user_repository->getByEmailOrName($term);
        $list  = array();

        if(count($users) > 0)
        {

            foreach($users as $u)
            {
                array_push($list, array
                    (
                        'id' => $u->id,
                        'value' => sprintf('%s', $u->getFullName())
                    )
                );
            }

        }
        return $this->ok($list);
    }
}