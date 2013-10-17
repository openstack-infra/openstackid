<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 5:26 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\helpers;
use openid\responses\contexts\ResponseContext;
use openid\responses\OpenIdPositiveAssertionResponse;

class OpenIdSignatureBuilder {

    public static function build(ResponseContext $context,$macAlg,$secret,OpenIdPositiveAssertionResponse &$response){
        //do signing ...
        $signed = '';
        $data = '';
        $params = $context->getSignParams();

        foreach($params as $key => $val){
            if (strpos($key, 'openid.') === 0) {
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
        $sig   = base64_encode(OpenIdCryptoHelper::computeHMAC($macAlg, $data, $secret));

        $response->setSigned($signed);
        $response->setSig($sig);
    }
}