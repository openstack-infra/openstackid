<?php namespace App\Http\Controllers\Api\OAuth2;
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
use Illuminate\Support\Facades\Response;
use OAuth2\Builders\IdTokenBuilder;
use OAuth2\IResourceServerContext;
use OAuth2\ResourceServer\IUserService;
use OAuth2\Services\IClientService;
use Utils\Http\HttpContentType;
use Utils\Services\ILogService;

/**
 * Class OAuth2UserApiController
 * @package App\Http\Controllers\Api\OAuth2
 */
class OAuth2UserApiController extends OAuth2ProtectedController
{
    /**
     * @var IUserService
     */
    private $user_service;

    /**
     * @var IClientService
     */
    private $client_service;

    /**
     * @var IdTokenBuilder
     */
    private $id_token_builder;

    /**
     * @param IUserService $user_service
     * @param IResourceServerContext $resource_server_context
     * @param ILogService $log_service
     * @param IClientService $client_service
     * @param IdTokenBuilder $id_token_builder
     */
    public function __construct
    (
        IUserService $user_service,
        IResourceServerContext $resource_server_context,
        ILogService $log_service,
        IClientService $client_service,
        IdTokenBuilder $id_token_builder
    )
    {
        parent::__construct($resource_server_context,$log_service);

        $this->user_service     = $user_service;
        $this->client_service   = $client_service;
        $this->id_token_builder = $id_token_builder;
    }

    /**
     * Gets User Basic Info
     * @return mixed
     */
    public function me()
    {
        try
        {
            $data = $this->user_service->getCurrentUserInfo();
            return $this->ok($data);
        }
        catch(Exception $ex)
        {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }

    public function userInfo()
    {
        try
        {
            $claims    = $this->user_service->getCurrentUserInfoClaims();
            $client_id = $this->resource_server_context->getCurrentClientId();
            $client    = $this->client_service->getClientById($client_id);

            // The UserInfo Claims MUST be returned as the members of a JSON object unless a signed or encrypted response
            // was requested during Client Registration.
            $user_info_response_info = $client->getUserInfoResponseInfo();

            $sig_alg = $user_info_response_info->getSigningAlgorithm();
            $enc_alg = $user_info_response_info->getEncryptionKeyAlgorithm();
            $enc     = $user_info_response_info->getEncryptionContentAlgorithm();

            if($sig_alg || ($enc_alg && $enc) )
            {
                $jwt = $this->id_token_builder->buildJWT($claims, $user_info_response_info, $client);
                $http_response = Response::make($jwt->toCompactSerialization(), 200);
                $http_response->header('Content-Type', HttpContentType::JWT);
                $http_response->header('Cache-Control','no-cache, no-store, max-age=0, must-revalidate');
                $http_response->header('Pragma','no-cache');
                return $http_response;
            }
            else
            {
                // return plain json
                return $this->ok( $claims->toArray() );
            }
        }
        catch(Exception $ex)
        {
            $this->log_service->error($ex);
            return $this->error500($ex);
        }
    }


}