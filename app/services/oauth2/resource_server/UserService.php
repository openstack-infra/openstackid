<?php

namespace services\oauth2\resource_server;

use Exception;
use jwt\impl\JWTClaimSet;
use jwt\JWTClaim;
use oauth2\IResourceServerContext;
use oauth2\resource_server\IUserService;
use oauth2\resource_server\OAuth2ProtectedService;
use oauth2\services\IClientService;
use openid\services\IUserService as IAPIUserService;
use utils\json_types\JsonValue;
use utils\json_types\StringOrURI;
use utils\services\IAuthService;
use utils\services\ILogService;
use utils\services\IServerConfigurationService;

/**
 * Class UserService
 * OAUTH2 Protected Endpoint
 * @package services\oauth2\resource_server
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
                $data['country'] = $current_user->getCountry();
                $data['street_address'] = $current_user->getCountry();
                $data['postal_code'] = $current_user->getPostalCode();
                $data['region'] = $current_user->getRegion();
                $data['locality'] = $current_user->getLocality();
            }
            if (in_array(self::UserProfileScope_Profile, $scopes)) {
                // Profile Claims
                $assets_url = $this->configuration_service->getConfigValue('Assets.Url');
                $pic_url = $current_user->getPic();
                $pic_url = str_contains($pic_url, 'http') ? $pic_url : $assets_url . $pic_url;
                $data['name'] = $current_user->getFirstName();
                $data['family_name'] = $current_user->getLastName();
                $data['nickname'] = $current_user->getNickName();
                $data['picture'] = $pic_url;
                $data['birthdate'] = $current_user->getDateOfBirth();
                $data['gender'] = $current_user->getGender();
            }
            if (in_array(self::UserProfileScope_Email, $scopes)) {
                // Email Claim
                $data['email'] = $current_user->getEmail();
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
                $claim_set->addClaim(new JWTClaim('country', new StringOrURI($current_user->getCountry())));
                $claim_set->addClaim(new JWTClaim('street_address', new StringOrURI($current_user->getCountry())));
                $claim_set->addClaim(new JWTClaim('postal_code', new StringOrURI($current_user->getPostalCode())));
                $claim_set->addClaim(new JWTClaim('region', new StringOrURI($current_user->getRegion())));
                $claim_set->addClaim(new JWTClaim('locality', new StringOrURI($current_user->getLocality())));
            }
            if (in_array(self::UserProfileScope_Profile, $scopes))
            {
                // Profile Claims
                $assets_url = $this->configuration_service->getConfigValue('Assets.Url');
                $pic_url = $current_user->getPic();
                $pic_url = str_contains($pic_url, 'http') ? $pic_url : $assets_url . $pic_url;

                $claim_set->addClaim(new JWTClaim('name', new StringOrURI($current_user->getFirstName())));
                $claim_set->addClaim(new JWTClaim('family_name', new StringOrURI($current_user->getLastName())));
                $claim_set->addClaim(new JWTClaim('nickname', new StringOrURI($current_user->getNickName())));
                $claim_set->addClaim(new JWTClaim('picture', new StringOrURI($pic_url)));
                $claim_set->addClaim(new JWTClaim('birthdate', new StringOrURI($current_user->getDateOfBirth())));
                $claim_set->addClaim(new JWTClaim('gender', new StringOrURI($current_user->getGender())));
            }
            if (in_array(self::UserProfileScope_Email, $scopes))
            {
                // Address Claim
                $claim_set->addClaim(new JWTClaim('email', new StringOrURI($current_user->getEmail())));
                $claim_set->addClaim(new JWTClaim('email_verified', new JsonValue(true)));
            }
        } catch (Exception $ex) {
            $this->log_service->error($ex);
            throw $ex;
        }
        return $claim_set;
    }
}