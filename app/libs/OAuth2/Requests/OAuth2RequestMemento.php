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
use ReflectionObject;

/**
 * Class OAuth2RequestMemento
 * @package OAuth2\Requests
 */
class OAuth2RequestMemento
{
    /**
     * @var array
     */
    protected $state  = array();

    /**
     * @param array $state
     */
    protected function __construct(array $state)
    {
        $this->state = $state;
    }

    /**
     * @return array
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param OAuth2Message $request
     * @return OAuth2RequestMemento
     */
    static public function buildFromRequest(OAuth2Message $request)
    {
        $r = new ReflectionObject($request);
        $p = $r->getProperty('container');
        $p->setAccessible(true);
        return new self($p->getValue($request));
    }

    /**
     * @param array $state
     * @return OAuth2RequestMemento
     */
    static public function buildFromState(array $state){
        return new self($state);
    }
}