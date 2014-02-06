<?php

namespace services\oauth2\resource_server;

use oauth2\resource_server\IUserService;
use oauth2\resource_server\OAuth2ProtectedService;
use oauth2\IResourceServerContext;
use utils\services\ILogService;
use openid\services\IUserService as IAPIUserService;
use Exception;

/**
 * Class UserService
 * OAUTH2 Protected Endpoint
 * @package services\oauth2\resource_server
 */
class UserService extends OAuth2ProtectedService implements IUserService {

    private $user_service;

    public function __construct(IAPIUserService $user_service, IResourceServerContext $resource_server_context, ILogService $log_service){
        parent::__construct($resource_server_context,$log_service);
        $this->user_service = $user_service;
    }

    /**
     * Get Current user info
     * @return array
     * @throws Exception
     */
    public function getCurrentUserInfo()
    {
        $data         = array();
        try{

            $me = $this->resource_server_context->getCurrentUserId();

            if(is_null($me)){
                throw new Exception('me is no set!.');
            }

            $current_user = $this->user_service->get($me);
            $scopes       = $this->resource_server_context->getCurrentScope();

            if(in_array(self::UserProfileScope_Address, $scopes)){
                // Address Claim
                $data['country']        = $current_user->getCountry();
                $data['street_address'] = $current_user->getCountry();
                $data['postal_code']    = $current_user->getPostalCode();
                $data['region']         = $current_user->getRegion();
                $data['locality']       = $current_user->getLocality();
            }
            if(in_array(self::UserProfileScope_Profile, $scopes)){
                // Address Claim
                $data['name']         = $current_user->getFirstName();
                $data['family_name']  = $current_user->getLastName();
                $data['nickname']     = $current_user->getNickName();
                $data['picture']      = $current_user->getPic();
                $data['birthdate']    = $current_user->getDateOfBirth();
                $data['gender']       = $current_user->getGender();
            }
            if(in_array(self::UserProfileScope_Email, $scopes)){
                // Address Claim
                $data['email']        = $current_user->getEmail();
            }
        }
        catch(Exception $ex){
            $this->log_service->error($ex);
            throw $ex;
        }
        return $data;
    }
}