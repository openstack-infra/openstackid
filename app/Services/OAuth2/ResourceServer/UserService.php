<?php namespace Services\OAuth2\ResourceServer;
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
use Exception;
use jwt\impl\JWTClaimSet;
use jwt\JWTClaim;
use OAuth2\AddressClaim;
use OAuth2\IResourceServerContext;
use OAuth2\ResourceServer\IUserService;
use OAuth2\ResourceServer\OAuth2ProtectedService;
use OAuth2\Services\IClientService;
use OAuth2\StandardClaims;
use OpenId\Services\IUserService as IAPIUserService;
use utils\json_types\JsonValue;
use utils\json_types\StringOrURI;
use Utils\Services\IAuthService;
use Utils\Services\ILogService;
use Utils\Services\IServerConfigurationService;

/**
 * Class UserService
 * OAUTH2 Protected Endpoint
 * @package Services\OAuth2\ResourceServer
 */
class UserService extends OAuth2ProtectedService implements IUserService
{
    /**
     * @var IAPIUserService
     */
    private $user_service;
    /**
     * @var IServerConfigurationService
     */
    private $configuration_service;

    /**
     * @var IClientService
     */
    private $client_service;

    /**
     * @var IAuthService
     */
    private $auth_service;

    public function __construct
    (
        IAPIUserService $user_service,
        IResourceServerContext $resource_server_context,
        IServerConfigurationService $configuration_service,
        ILogService $log_service,
        IClientService $client_service,
        IAuthService $auth_service
    )
    {
        parent::__construct($resource_server_context, $log_service);

        $this->user_service          = $user_service;
        $this->configuration_service = $configuration_service;
        $this->client_service        = $client_service;
        $this->auth_service          = $auth_service;
    }

    /**
     * Get Current user info
     * @return array
     * @throws Exception
     */
    public function getCurrentUserInfo()
    {
        $data = array();
        try
        {

            $me = $this->resource_server_context->getCurrentUserId();

            if (is_null($me)) {
                throw new Exception('me is no set!.');
            }

            $current_user = $this->user_service->get($me);
            $scopes = $this->resource_server_context->getCurrentScope();

            if (in_array(self::UserProfileScope_Address, $scopes)) {
                // Address Claims
                $data[AddressClaim::Country]       = $current_user->getCountry();
                $data[AddressClaim::StreetAddress] = $current_user->getCountry();
                $data[AddressClaim::PostalCode]    = $current_user->getPostalCode();
                $data[AddressClaim::Region]        = $current_user->getRegion();
                $data[AddressClaim::Locality]      = $current_user->getLocality();
            }
            if (in_array(self::UserProfileScope_Profile, $scopes)) {
                // Profile Claims
                $assets_url = $this->configuration_service->getConfigValue('Assets.Url');
                $pic_url = $current_user->getPic();
                $pic_url = str_contains($pic_url, 'http') ? $pic_url : $assets_url . $pic_url;

                $data[StandardClaims::Name]       = $current_user->getFullName();
                $data[StandardClaims::GivenName]  = $current_user->getFirstName();
                $data[StandardClaims::FamilyName] = $current_user->getLastName();
                $data[StandardClaims::NickName]   = $current_user->getNickName();
                $data[StandardClaims::Picture]    = $pic_url;
                $data[StandardClaims::Birthdate]  = $current_user->getDateOfBirth();
                $data[StandardClaims::Gender]     = $current_user->getGender();
            }
            if (in_array(self::UserProfileScope_Email, $scopes)) {
                // Email Claim
                $data[StandardClaims::Email]         = $current_user->getEmail();
                $data[StandardClaims::EmailVerified] = $current_user->isEmailVerified();
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }

        return $data;
    }

    /**
     * @return JWTClaimSet
     * @throws Exception
     */
    public function getCurrentUserInfoClaims()
    {
        try
        {

            $me        = $this->resource_server_context->getCurrentUserId();
            $client_id = $this->resource_server_context->getCurrentClientId();
            $client    = $this->client_service->getClientById($client_id);

            if (is_null($me))
            {
                throw new Exception('me is no set!.');
            }

            $current_user = $this->user_service->get($me);
            $scopes       = $this->resource_server_context->getCurrentScope();

            $claim_set = new JWTClaimSet
            (
                null,
                $sub = new StringOrURI
                (
                    $this->auth_service->wrapUserId
                    (
                        $current_user->getExternalIdentifier(),
                        $client
                    )
                ),
                $aud = new StringOrURI($client_id)

            );

            if (in_array(self::UserProfileScope_Address, $scopes)) {
                // Address Claims
                $address = array();
                $address[AddressClaim::Country]       = $current_user->getCountry();
                $address[AddressClaim::StreetAddress] = $current_user->getStreetAddress();
                $address[AddressClaim::PostalCode]    = $current_user->getPostalCode();
                $address[AddressClaim::Region]        = $current_user->getRegion();
                $address[AddressClaim::Locality]      = $current_user->getLocality();
                $address[AddressClaim::Formatted]     = $current_user->getFormattedAddress();

                $claim_set->addClaim(new JWTClaim(StandardClaims::Address, new JsonValue($address)));

            }
            if (in_array(self::UserProfileScope_Profile, $scopes))
            {
                // Profile Claims
                $assets_url = $this->configuration_service->getConfigValue('Assets.Url');
                $pic_url = $current_user->getPic();
                $pic_url = str_contains($pic_url, 'http') ? $pic_url : $assets_url . $pic_url;

                $claim_set->addClaim(new JWTClaim(StandardClaims::Name, new StringOrURI($current_user->getFullName())));
                $claim_set->addClaim(new JWTClaim(StandardClaims::GivenName, new StringOrURI($current_user->getFirstName())));
                $claim_set->addClaim(new JWTClaim(StandardClaims::FamilyName, new StringOrURI($current_user->getLastName())));
                $claim_set->addClaim(new JWTClaim(StandardClaims::NickName, new StringOrURI($current_user->getNickName())));
                $claim_set->addClaim(new JWTClaim(StandardClaims::Picture, new StringOrURI($pic_url)));
                $claim_set->addClaim(new JWTClaim(StandardClaims::Birthdate, new StringOrURI($current_user->getDateOfBirth())));
                $claim_set->addClaim(new JWTClaim(StandardClaims::Gender, new StringOrURI($current_user->getGender())));
                $claim_set->addClaim(new JWTClaim(StandardClaims::Locale, new StringOrURI($current_user->getLanguage())));
            }
            if (in_array(self::UserProfileScope_Email, $scopes))
            {
                // Address Claim
                $claim_set->addClaim(new JWTClaim(StandardClaims::Email, new StringOrURI($current_user->getEmail())));
                $claim_set->addClaim(new JWTClaim(StandardClaims::EmailVerified, new JsonValue($current_user->isEmailVerified())));
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
        return $claim_set;
    }
}