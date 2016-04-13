<?php namespace Models\OAuth2;
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
use OAuth2\Models\IUserConsent;
use Utils\Model\BaseModelEloquent;
use OAuth2\Models\IClient;
use Auth\User;
/**
 * Class UserConsent
 * @package Models\OAuth2
 */
class UserConsent extends BaseModelEloquent implements IUserConsent {

    protected $table = 'oauth2_user_consents';

    public function user()
    {
        return $this->belongsTo('Auth\User');
    }

    public function client()
    {
        return $this->belongsTo('Models\OAuth2\Client');
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return IClient
     */
    public function getClient()
    {
        return $this->client()->first();
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user()->first();
    }
}