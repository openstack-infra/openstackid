<?php

namespace openid\services;

use openid\model\OpenIdNonce;

interface INonceService
{

    /**
     * @return OpenIdNonce
     */
    public function generateNonce();

    public function lockNonce(OpenIdNonce $nonce);

    public function unlockNonce(OpenIdNonce $nonce);

    public function associateNonce(OpenIdNonce $nonce, $signature, $realm);

    /**
     * To prevent replay attacks, the OP MUST NOT issue more than one verification response
     * for each authentication response it had previously issued. An authentication response
     * and its matching verification request may be identified by their "openid.response_nonce" values.
     * @param OpenIdNonce $nonce
     * @param $signature
     * @param $realm
     * @return mixed
     */
    public function markNonceAsInvalid(OpenIdNonce $nonce, $signature, $realm);
} 