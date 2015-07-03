<?php

namespace oauth2\responses;

use oauth2\models\IClient;
use oauth2\OAuth2Protocol;
use openid\model\IOpenIdUser;
use utils\http\HttpContentType;

/**
 * Class OAuth2AccessTokenValidationResponse
 * @package oauth2\responses
 */
class OAuth2AccessTokenValidationResponse extends OAuth2DirectResponse {

    /**
     * @param array|int $access_token
     * @param string $scope
     * @param $audience
     * @param IClient $client
     * @param $expires_in
     * @param IOpenIdUser|null $user
     * @param array $allowed_urls
     * @param array $allowed_origins
     */
    public function __construct
    (
        $access_token,
        $scope,
        $audience,
        IClient $client,
        $expires_in,
        IOpenIdUser $user = null,
        $allowed_urls = array(),
        $allowed_origins = array()
    )
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, HttpContentType::Json);
        $this[OAuth2Protocol::OAuth2Protocol_AccessToken]           = $access_token;
        $this[OAuth2Protocol::OAuth2Protocol_ClientId]              = $client->getClientId();
        $this['application_type']                                   = $client->getApplicationType();
        $this[OAuth2Protocol::OAuth2Protocol_TokenType]             = 'Bearer';
        $this[OAuth2Protocol::OAuth2Protocol_Scope]                 = $scope;
        $this[OAuth2Protocol::OAuth2Protocol_Audience]              = $audience;
        $this[OAuth2Protocol::OAuth2Protocol_AccessToken_ExpiresIn] = $expires_in;

        if(!is_null($user))
        {
            $this[OAuth2Protocol::OAuth2Protocol_UserId] = $user->getId();
            $this['user_external_id']                    = $user->getExternalIdentifier();
        }

        if(count($allowed_urls)){
            $this['allowed_return_uris'] = implode(' ', $allowed_urls);
        }

        if(count($allowed_origins)){
            $this['allowed_origins'] = implode(' ', $allowed_origins);
        }
    }
} 