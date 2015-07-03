<?php

namespace oauth2\responses;

use utils\http\HttpContentType;

class OAuth2DirectResponse extends OAuth2Response
{

    const OAuth2DirectResponse      = 'OAuth2DirectResponse';

    public function __construct($http_code = self::HttpOkResponse, $content_type = HttpContentType::Json)
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