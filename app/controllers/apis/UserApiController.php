<?php

use utils\services\ILogService;
use openid\services\IUserService;
use oauth2\services\ITokenService;
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
                    if(is_null($token->getUserId()) || intval($token->getUserId())!=intval($id))
                        throw new Exception(sprintf("access token %s does not belongs to user id %s!.",$value,$id));
                    $this->token_service->revokeAccessToken($value,true);
                }
                    break;
                case 'refresh_token':
                    $token = $this->token_service->getRefreshToken($value,true);
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
        catch(Exception $ex){
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }


    public function get($id)
    {
        // TODO: Implement get() method.
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