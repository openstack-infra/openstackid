<?php namespace OpenId\Services;
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
use OpenId\Models\OpenIdNonce;
/**
 * Interface INonceService
 * @package OpenId\Services
 */
interface INonceService {

    /**
     * @return OpenIdNonce
     */
    public function generateNonce();

    /**
     * @param OpenIdNonce $nonce
     * @return $this
     */
    public function lockNonce(OpenIdNonce $nonce);

    /**
     * @param OpenIdNonce $nonce
     * @return $this
     */
    public function unlockNonce(OpenIdNonce $nonce);

    /**
     * @param OpenIdNonce $nonce
     * @param string $signature
     * @param string $realm
     * @return $this
     */
    public function associateNonce(OpenIdNonce $nonce, $signature, $realm);

    /**
     * To prevent replay attacks, the OP MUST NOT issue more than one verification response
     * for each authentication response it had previously issued. An authentication response
     * and its matching verification request may be identified by their "openid.response_nonce" values.
     * @param OpenIdNonce $nonce
     * @param string $signature
     * @param string $realm
     * @return $this
     */
    public function markNonceAsInvalid(OpenIdNonce $nonce, $signature, $realm);
} 