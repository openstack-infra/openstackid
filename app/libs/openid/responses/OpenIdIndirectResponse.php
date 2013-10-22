<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 11:17 AM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\responses;
use openid\OpenIdProtocol;
use openid\responses\OpenIdResponse;

class OpenIdIndirectResponse extends OpenIdResponse {

    const IndirectResponseContentType ="application/x-www-form-urlencoded";
    const OpenIdIndirectResponse="OpenIdIndirectResponse";

    public function __construct(){
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse,self::IndirectResponseContentType);
        /*
         * This particular value MUST be present for the response to be a valid OpenID 2.0
         * response. Future versions of the specification may define different values in order
         * to allow message recipients to properly interpret the request.
         */
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_NS)] = OpenIdProtocol::OpenID2MessageType;
    }


    public function getContent()
    {
        $url_encoded_format ="";
        if ($this->container !== null) {
            ksort($this->container);
            foreach ($this->container as $key => $value) {
                if (is_array($value)) {
                    list($key, $value) = array($value[0], $value[1]);
                }
                $value=urlencode ($value);
                $url_encoded_format .= "$key=$value&";
            }
            $url_encoded_format = rtrim($url_encoded_format,'&');
        }
        return $url_encoded_format;
    }

    public function getType()
    {
        return self::OpenIdIndirectResponse;
    }

    public function setReturnTo($return_to){
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)] = $return_to;
    }

    public function getReturnTo(){
        return $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)];
    }
}