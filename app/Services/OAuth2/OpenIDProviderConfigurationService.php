<?php namespace Services\OAuth2;
/**
 * Copyright 2016 OpenStack Foundation
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
use OAuth2\Discovery\IOpenIDProviderConfigurationService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

/**
 * Class OpenIDProviderConfigurationService
 * @package Services\OAuth2
 */
final class OpenIDProviderConfigurationService implements IOpenIDProviderConfigurationService
{

    /**
     * @return string
     */
    public function getIssuerUrl()
    {
        return Config::get('app.url');
    }

    /**
     * @return string
     */
    public function getAuthEndpoint()
    {
        return URL::action("OAuth2\OAuth2ProviderController@auth");
    }

    /**
     * @return string
     */
    public function getTokenEndpoint()
    {
        return URL::action("OAuth2\OAuth2ProviderController@token");
    }

    /**
     * @return string
     */
    public function getUserInfoEndpoint()
    {
        return URL::action("Api\OAuth2\OAuth2UserApiController@userInfo");
    }

    /**
     * @return string
     */
    public function getJWKSUrl()
    {
        return URL::action("OAuth2\OAuth2ProviderController@certs");
    }

    /**
     * @return string
     */
    public function getRevocationEndpoint()
    {
        return URL::action("OAuth2\OAuth2ProviderController@revoke");
    }

    /**
     * @return string
     */
    public function getIntrospectionEndpoint()
    {
        return URL::action("OAuth2\OAuth2ProviderController@introspection");
    }

    /**
     * @return string
     */
    public function getCheckSessionIFrame()
    {
        return URL::action("OAuth2\OAuth2ProviderController@checkSessionIFrame");
    }

    /**
     * @return string
     */
    public function getEndSessionEndpoint()
    {
        return URL::action("OAuth2\OAuth2ProviderController@endSession");
    }
}