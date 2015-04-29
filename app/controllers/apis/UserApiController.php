<?php
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

use utils\services\ILogService;
use openid\services\IUserService;
use oauth2\services\ITokenService;
use oauth2\exceptions\ExpiredAccessTokenException;

/**
 * Class UserApiController
 */
class UserApiController extends AbstractRESTController implements ICRUDController {

    private $user_service;
    private $token_service;

    public function __construct(ILogService $log_service, IUserService $user_service,ITokenService $token_service){
        parent::__construct($log_service);
        $this->user_service   = $user_service;
        $this->token_service = $token_service;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function unlock($id){
        try {
            $res = $this->user_service->unlockUser($id);
            return $res ? $this->ok() : $this->error404(array('error' => 'operation failed'));
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
}