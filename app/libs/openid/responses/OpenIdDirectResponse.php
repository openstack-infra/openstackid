<?php

namespace openid\responses;

use openid\exceptions\InvalidKVFormat;
use openid\helpers\OpenIdErrorMessages;
use openid\OpenIdProtocol;
use utils\http\HttpContentType;

/**
 * Class OpenIdDirectResponse
 * Implementation of 5.1.2. Direct Response
 * @package openid\responses
 */
class OpenIdDirectResponse extends OpenIdResponse
{

    const OpenIdDirectResponse = "OpenIdDirectResponse";

    public function __construct()
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, HttpContentType::Text);
        /*
         * This particular value MUST be present for the response to be a valid OpenID 2.0
         * response. Future versions of the specification may define different values in order
         * to allow message recipients to properly interpret the request.
         */
        $this["ns"] = OpenIdProtocol::OpenID2MessageType;
    }

    /**
     * Implementation of 4.1.1.  Key-Value Form Encoding
     * @return string
     * @throws \openid\exceptions\InvalidKVFormat
     */
    public function getContent()
    {
        $kv_format = "";
        if ($this->container !== null) {
            ksort($this->container);
            foreach ($this->container as $key => $value) {
                if (is_array($value)) {
                    list($key, $value) = array($value[0], $value[1]);
                }

                if (strpos($key, ':') !== false) {
                    throw new InvalidKVFormat(sprintf(OpenIdErrorMessages::InvalidKVFormatChar, $key, ':'));
                }

                if (strpos($key, "\n") !== false) {
                    throw new InvalidKVFormat(sprintf(OpenIdErrorMessages::InvalidKVFormatChar, $key, '\\n'));
                }

                if (strpos($value, "\n") !== false) {
                    throw new InvalidKVFormat(sprintf(OpenIdErrorMessages::InvalidKVFormatChar, $value, '\\n'));
                }
                $kv_format .= "$key:$value\n";
            }
        }
        return $kv_format;
    }

    public function getType()
    {
        return self::OpenIdDirectResponse;
    }
}