<?php namespace Factories;
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

use OAuth2\Factories\IOAuth2ClientFactory;
use OAuth2\Models\IClient;
use Models\OAuth2\Client;
use OAuth2\OAuth2Protocol;

/**
 * Class OAuth2ClientFactory
 * @package Factories
 */
final class OAuth2ClientFactory implements IOAuth2ClientFactory
{

    /**
     * @param string $app_name
     * @param $owner
     * @param string $application_type
     * @return IClient
     */
    public function build($app_name, $owner, $application_type)
    {
        $client = new Client
        (
            array
            (
                'max_auth_codes_issuance_basis'    => 0,
                'max_refresh_token_issuance_basis' => 0,
                'max_access_token_issuance_qty'    => 0,
                'max_access_token_issuance_basis'  => 0,
                'max_refresh_token_issuance_qty'   => 0,
                'use_refresh_token'                => false,
                'rotate_refresh_token'             => false,
            )
        );

        $client->setOwner($owner);

        $client->app_name             = $app_name;
        $client->active               = true;
        $client->use_refresh_token    = false;
        $client->rotate_refresh_token = false;

        $client->application_type = $application_type;

        if ($client->client_type === IClient::ClientType_Confidential)
        {
            $client->token_endpoint_auth_method = OAuth2Protocol::TokenEndpoint_AuthMethod_ClientSecretBasic;
        }
        else
        {
            $client->token_endpoint_auth_method = OAuth2Protocol::TokenEndpoint_AuthMethod_None;
        }

        return $client;
    }
}