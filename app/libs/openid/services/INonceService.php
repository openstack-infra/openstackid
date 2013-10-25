<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/24/13
 * Time: 9:06 PM
 */

namespace openid\services;
use \openid\exceptions\ReplayAttackException;

interface INonceService {

    public function generateNonce();

    public function associateNonce($nonce,$signature);

    /**
     * To prevent replay attacks, the OP MUST NOT issue more than one verification response
     * for each authentication response it had previously issued. An authentication response
     * and its matching verification request may be identified by their "openid.response_nonce" values.
     * @param $nonce
     * @param $signature
     * @throws ReplayAttackException
     * @return mixed
     */
    public function markNonceAsInvalid($nonce,$signature);
} 