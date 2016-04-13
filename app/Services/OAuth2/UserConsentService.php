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
use Models\OAuth2\Client;
use OAuth2\Exceptions\AbsentClientException;
use OAuth2\Models\IUserConsent;
use OAuth2\Services\IUserConsentService;
use Models\OAuth2\UserConsent;
use Utils\MathUtils;
/**
 * Class UserConsentService
 * @package Services\OAuth2
 */
class UserConsentService implements IUserConsentService
{

    /**
     * @param int $user_id
     * @param string $client_id
     * @param string $scopes
     * @return IUserConsent
     */
    public function get($user_id, $client_id, $scopes)
    {

        $set = explode(' ', $scopes);
        $size = count($set) - 1;
        $perm = range(0, $size);
        $j = 0;
        $perms = array();

        do
        {
            foreach ($perm as $i)
            {
                $perms[$j][] = $set[$i];
            }
        } while ($perm = MathUtils::nextPermutation($perm, $size) and ++$j);


        $query1 = UserConsent::where('user_id', '=', $user_id)->where('client_id', '=', $client_id);

        $query2 = UserConsent::where('user_id', '=', $user_id)->where('client_id', '=', $client_id);


        $query1 = $query1->where(function ($query) use($perms)
        {
            foreach ($perms as $p)
            {
                $str = join(' ', $p);
                $query = $query->orWhere('scopes', '=', $str);
            }

            return $query;
        });


        $query2 = $query2->where(function ($query) use($perms)
        {
            foreach ($perms as $p)
            {
                $str = join(' ', $p);
                $query = $query->orWhere('scopes', 'like', '%'.$str.'%');
            }

            return $query;
        });


        $consent = $query1->first();

        if (is_null($consent)) {
            $consent = $query2->first();
        }

        return $consent;
    }

    /**
     * @param int $user_id
     * @param string $client_id
     * @param string $scopes
     * @return IUserConsent|void
     * @throws AbsentClientException
     */
    public function add($user_id, $client_id, $scopes)
    {
        $consent = new UserConsent();

        if (is_null(Client::find($client_id))) {
            throw new AbsentClientException;
        }

        $consent->client_id = $client_id;
        $consent->user_id = $user_id;
        $consent->scopes = $scopes;
        $consent->Save();
    }
}