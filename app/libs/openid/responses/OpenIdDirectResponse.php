<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/15/13
 * Time: 10:30 AM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\responses;
use openid\exceptions\InvalidKVFormat;

/**
 * Class OpenIdDirectResponse
 * Implementation of 5.1.2. Direct Response
 * @package openid\responses
 */
class OpenIdDirectResponse  extends OpenIdResponse {

   const OpenIdDirectResponse="OpenIdDirectResponse";

   const DirectResponseContentType ="text/plain";

   public function __construct(){
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse,self::DirectResponseContentType);
        /*
         * This particular value MUST be present for the response to be a valid OpenID 2.0
         * response. Future versions of the specification may define different values in order
         * to allow message recipients to properly interpret the request.
         */
        $this["ns"]=self::OpenId2ResponseType;
    }
    /**
     * Implementation of 4.1.1.  Key-Value Form Encoding
     * @return string
     * @throws \openid\exceptions\InvalidKVFormat
     */
    public function getContent()
    {
        $kv_format ="";
        if ($this->container !== null) {
            ksort($this->container);
            foreach ($this->container as $key => $value) {
                if (is_array($value)) {
                    list($key, $value) = array($value[0], $value[1]);
                }

                if (strpos($key, ':') !== false) {
                    throw new InvalidKVFormat("key ".$key." has invalid char (':')");
                }

                if (strpos($key, "\n") !== false) {
                    throw new InvalidKVFormat("key ".$key." has invalid char ('\\n')");
                }

                if (strpos($value, "\n") !== false) {
                    throw new InvalidKVFormat("value ".$value." has invalid char ('\\n')");
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