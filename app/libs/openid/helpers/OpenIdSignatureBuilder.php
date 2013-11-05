<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 5:26 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\helpers;

use openid\requests\OpenIdCheckAuthenticationRequest;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdPositiveAssertionResponse;

class OpenIdSignatureBuilder
{


    /**
     * @param OpenIdCheckAuthenticationRequest $request
     * @param $macAlg
     * @param $secret
     * @param $claimed_sig
     * @return bool
     */
    public static function verify(OpenIdCheckAuthenticationRequest $request, $macAlg, $secret, $claimed_sig)
    {
        $res = false;
        $signed = $request->getSigned();
        $claimed_signed = explode(',', $signed);
        $data = '';
        foreach ($claimed_signed as $key) {
            $key_php = str_ireplace('.', '_', $key);
            $val = $request->getParam($key_php);
            $data .= $key . ':' . $val . "\n";
        }
        $computed_sig = base64_encode(OpenIdCryptoHelper::computeHMAC($macAlg, $data, $secret));
        if ($claimed_sig == $computed_sig)
            $res = true;
        return $res;
    }

    /**
     * @param ResponseContext $context
     * @param $macAlg
     * @param $secret
     * @param OpenIdPositiveAssertionResponse $response
     */
    public static function build(ResponseContext $context, $macAlg, $secret, OpenIdPositiveAssertionResponse &$response)
    {
        //do signing ...
        $signed = '';
        $data = '';
        $params = $context->getSignParams();

        foreach ($params as $key) {
            if (strpos($key, 'openid.') === 0) {
                $val = $response[$key];
                $key = substr($key, strlen('openid.'));
                if (!empty($signed)) {
                    $signed .= ',';
                }
                $signed .= $key;
                $data .= $key . ':' . $val . "\n";
            }
        }
        $signed .= ',signed';
        $data .= 'signed:' . $signed . "\n";
        $sig = base64_encode(OpenIdCryptoHelper::computeHMAC($macAlg, $data, $secret));

        $response->setSigned($signed);
        $response->setSig($sig);
    }
}