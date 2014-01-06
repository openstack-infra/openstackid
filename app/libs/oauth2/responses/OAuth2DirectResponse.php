<?php

namespace oauth2\responses;

class OAuth2DirectResponse extends OAuth2Response {

    const DirectResponseContentType = "application/json;charset=UTF-8";
    const OAuth2DirectResponse      = 'OAuth2DirectResponse';

    public function __construct($http_code=self::HttpOkResponse, $content_type=self::DirectResponseContentType)
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct($http_code,$content_type );
    }

    public function getContent()
    {
        $json_encoded_format = json_encode($this->container);
        return $json_encoded_format;
    }

    public function getType()
    {
        return self::OAuth2DirectResponse;
    }
}