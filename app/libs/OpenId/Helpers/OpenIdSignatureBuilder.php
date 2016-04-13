<?php namespace OpenId\Helpers;
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
use OpenId\Requests\OpenIdCheckAuthenticationRequest;
use OpenId\Responses\Contexts\ResponseContext;
use OpenId\Responses\OpenIdPositiveAssertionResponse;
use Utils\Http\HttpMessage;
/**
 * Class OpenIdSignatureBuilder
 * @package OpenId\Helpers;
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
            $key_php = str_ireplace('.', HttpMessage::PHP_REQUEST_VAR_SEPARATOR, $key);
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