<?php

namespace oauth2\responses;

use utils\http\HttpContentType;

/**
 * Class OAuth2IndirectResponse
 * @package oauth2\responses
 */
abstract class OAuth2IndirectResponse extends OAuth2Response
{

    /**
     * @var string
     */
    protected $return_to;

    const OAuth2IndirectResponse      = "OAuth2IndirectResponse";

    public function __construct()
    {
        // Successful Responses: A server receiving a valid request MUST send a
        // response with an HTTP status code of 200.
        parent::__construct(self::HttpOkResponse, HttpContentType::Form);
    }

    public function getType()
    {
        return self::OAuth2IndirectResponse;
    }

    public function setReturnTo($return_to)
    {
        $this->return_to = $return_to;
    }

    public function getReturnTo()
    {
        return $this->return_to;
    }

    public function getContent()
    {
        $url_encoded_format = "";
        if ($this->container !== null)
        {
            ksort($this->container);
            foreach ($this->container as $key => $value)
            {
                if (is_array($value))
                {
                    list($key, $value) = array($value[0], $value[1]);
                }
                $value = urlencode($value);
                $url_encoded_format .= "$key=$value&";
            }
            $url_encoded_format = rtrim($url_encoded_format, '&');
        }
        return $url_encoded_format;
    }

    public function getContentType()
    {
        return HttpContentType::Form;
    }
} 