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
        return $this->message->getParam($param);
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