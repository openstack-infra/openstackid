<?php namespace OAuth2\Requests;

/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

use OAuth2\OAuth2Message;
/**
 * Class OAuth2Request
 * @package OAuth2\Requests
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
        if(!empty($value)) $value = trim(urldecode($value));
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
    public function __toString()
    {
        return $this->message->__toString();
    }

    /**
     * @return bool
     */
    public abstract function isValid();

    protected $last_validation_error = '';

    /**
     * @return string
     */
    public function getLastValidationError()
    {
        return $this->last_validation_error;
    }

} 