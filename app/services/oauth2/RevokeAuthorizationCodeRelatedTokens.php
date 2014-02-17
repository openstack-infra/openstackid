<?php

namespace services\oauth2;

use Exception;
use Log;
use oauth2\services\OAuth2ServiceCatalog;
use utils\services\ServiceLocator;
use utils\services\ISecurityPolicyCounterMeasure;


/**
 * Implements
 * http://tools.ietf.org/html/rfc6819#section-5.2.1.1
 * Automatic Revocation of Derived Tokens If Abuse Is Detected
 * If an authorization server observes multiple attempts to redeem an
 * authorization grant (e.g., such as an authorization "code"), the
 * authorization server may want to revoke all tokens granted based on
 * the authorization grant.
 * Class RevokeAuthorizationCodeRelatedTokens
 * @package services\oauth2
 */
class RevokeAuthorizationCodeRelatedTokens implements ISecurityPolicyCounterMeasure {

    public function trigger(array $params = array())
    {
        try {

            if (!isset($params["auth_code"])) return;
            $auth_code      = $params["auth_code"];
            $token_service  = ServiceLocator::getInstance()->getService(OAuth2ServiceCatalog::TokenService);
            $token_service->revokeAuthCodeRelatedTokens($auth_code);
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }
}