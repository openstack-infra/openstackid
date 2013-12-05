<?php

namespace utils\http;


abstract class HttpResponse extends HttpMessage
{
    const HttpOkResponse = 200;
    const HttpErrorResponse = 400;

    protected $http_code;
    protected $content_type;

    public function __construct($http_code, $content_type)
    {
        $this->http_code = $http_code;
        $this->content_type = $content_type;
    }

    abstract public function getContent();

    public function getHttpCode()
    {
        return $this->http_code;
    }

    protected function setHttpCode($http_code)
    {
        $this->http_code = $http_code;
    }

    public function getContentType()
    {
        return $this->content_type;
    }

    abstract public function getType();

    public function addParam($name, $value)
    {
        $this[$name] = $value;
    }
}