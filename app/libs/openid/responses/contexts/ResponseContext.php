<?php

namespace openid\responses\contexts;

/**
 * Class ResponseContext
 * @package openid\responses\contexts
 */
class ResponseContext {

    private $sign_params;

    public function __construct()
    {
        $this->sign_params = array();
    }

    public function addSignParam($param)
    {
        array_push($this->sign_params, $param);
    }

    public function getSignParams()
    {
        ksort($this->sign_params);
        return $this->sign_params;
    }
}