<?php namespace Services\SecurityPolicies;
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
use Illuminate\Support\Facades\Log;
use OAuth2\Services\ITokenService;
use Utils\Services\ISecurityPolicyCounterMeasure;
/**
 * Implements
 * @see http://tools.ietf.org/html/rfc6819#section-5.2.1.1
 * Automatic Revocation of Derived Tokens If Abuse Is Detected
 * If an authorization server observes multiple attempts to redeem an
 * authorization grant (e.g., such as an authorization "code"), the
 * authorization server may want to revoke all tokens granted based on
 * the authorization grant.
 * Class RevokeAuthorizationCodeRelatedTokens
 * @package Services\SecurityPolicies
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
     * @return $this|void
     */
    public function trigger(array $params = array())
    {
        try {
            if (isset($params["auth_code"])) {
                $auth_code = $params["auth_code"];
                $this->token_service->revokeAuthCodeRelatedTokens($auth_code);
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }
        return $this;
    }
}