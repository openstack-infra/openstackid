<?php namespace OpenId\Responses\Contexts;
/**
 * Class ResponseContext
 * @package OpenId\Responses\Contexts
 */
class ResponseContext {

    /**
     * @var array
     */
    private $sign_params;

    public function __construct()
    {
        $this->sign_params = array();
    }

    /**
     * @param string $param
     * @return $this
     */
    public function addSignParam($param)
    {
        array_push($this->sign_params, $param);
        return $this;
    }

    /**
     * @return array
     */
    public function getSignParams()
    {
        ksort($this->sign_params);
        return $this->sign_params;
    }
}