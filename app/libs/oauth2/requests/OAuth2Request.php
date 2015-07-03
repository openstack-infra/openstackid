<?php
namespace oauth2\requests;

use oauth2\OAuth2Message;

/**
 * Class OAuth2Request
 * @package oauth2\requests
 */
abstract class OAuth2Request {

    /**
     * @var OAuth2Message
     */
    protected $message;

    /**
     * @param OAuth2Message $msg
     */
    public function __construct(OAuth2Message $msg)
    {
        $this->message = $msg;
    }

    /**
     * @return OAuth2Message
     */
    public function getMessage(){
        return $this->message;
    }

    /**
     * @param string $param
     * @return null
     */
    public function getParam($param)
    {
        $value =  $this->message->getParam($param);
        if(!empty($value)) $value = urldecode($value);
        return $value;
    }

    /**
     * @param string $param
     * @param mixed $value
     * @return $this
     */
    public function setParam($param, $value)
    {
        $this->message->setParam($param, $value);
        return $this;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $string = $this->message->toString();
        return $string;
    }

    /**
     * @return bool
     */
    public abstract function isValid();

} 