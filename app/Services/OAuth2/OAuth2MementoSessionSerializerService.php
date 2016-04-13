<?php namespace Services\OAuth2;
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

use OAuth2\Requests\OAuth2RequestMemento;
use OAuth2\Services\IMementoOAuth2SerializerService;
use Illuminate\Support\Facades\Session;

/**
 * Class OAuth2MementoSessionSerializerService
 * @package Services\OAuth2
 */
final class OAuth2MementoSessionSerializerService implements IMementoOAuth2SerializerService
{

    /**
     * @param OAuth2RequestMemento $memento
     * @return void
     */
    public function serialize(OAuth2RequestMemento $memento)
    {
        $state = base64_encode(json_encode($memento->getState()));
        Session::put('oauth2.request.state', $state);
        Session::save();
    }

    /**
     * @return OAuth2RequestMemento
     */
    public function load()
    {
        $state = Session::get('oauth2.request.state', null);
        if(is_null($state)) return null;

        $state = json_decode( base64_decode($state), true);

        return OAuth2RequestMemento::buildFromState($state);
    }

    /**
     * @return void
     */
    public function forget()
    {
        Session::remove('oauth2.request.state');
        Session::save();
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return Session::has('oauth2.request.state');
    }
}