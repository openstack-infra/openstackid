<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/16/13
 * Time: 2:38 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\responses\contexts;


class ResponseContext
{

    private $sign_params;

    public function __construct()
    {
        $this->sign_params = array();
    }

    public function addSignParam(string $param)
    {
        array_push($this->sign_params, $param);
    }

    public function getSignParams()
    {
        ksort($this->sign_params);
        return $this->sign_params;
    }
}