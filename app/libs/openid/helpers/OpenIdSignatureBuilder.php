<?php

namespace openid\helpers;

use openid\requests\OpenIdCheckAuthenticationRequest;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdPositiveAssertionResponse;

/**
 * Class OpenIdSignatureBuilder
 * @package openid\helpers
 */
final class OpenIdSignatureBuilder
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
        ksort($claimed_signed);
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
            if (strpos($key, 'openid.') == 0) {
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