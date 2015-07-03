<?php
/**
 * Copyright 2015 OpenStack Foundation
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

namespace openid\requests;

use ReflectionObject;

use openid\OpenIdMessage;

/**
 * Class OpenIdMessageMemento
 * @package openid\requests
 */
final class OpenIdMessageMemento
{
    /**
     * @var array
     */
    protected $state  = array();
    /**
     * @param array $state
     */
    protected function __construct(array $state){
        $this->state = $state;
    }

    /**
     * @return array
     */
    public function getState(){
        return $this->state;
    }

    /**
     * @param OpenIdMessage $request
     * @return OpenIdMessageMemento
     */
    static public function buildFromRequest(OpenIdMessage $request){
        $r = new ReflectionObject($request);
        $p = $r->getProperty('container');
        $p->setAccessible(true);
        return new self($p->getValue($request));
    }

    /**
     * @param array $state
     * @return OpenIdMessageMemento
     */
    static public function buildFromState(array $state){
        return new self($state);
    }
}