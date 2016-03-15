<?php

namespace services;

use Exception;
use Log;
use oauth2\services\ITokenService;
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
 * @package services
 */
final class RevokeAuthorizationCodeRelatedTokens implements ISecurityPolicyCounterMeasure {

    /**
     * @var ITokenService
     */
	private $token_service;

	/**
	 * @param ITokenService $token_service
	 */
	public function __construct(ITokenService $token_service){
		$this->token_service = $token_service;
	}

    /**
     * @param array $params
     */
    public function trigger(array $params = array())
    {
        try {
            if (!isset($params["auth_code"])) return;
            $auth_code      = $params["auth_code"];
	        $this->token_service->revokeAuthCodeRelatedTokens($auth_code);
        } catch (Exception $ex) {
            Log::error($ex);
        }
    }
}