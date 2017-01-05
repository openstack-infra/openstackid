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
use Utils\Exceptions\EntityNotFoundException;
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
        $scope_set = explode(' ', $scopes);
        sort($scope_set);

        $consent  = UserConsent
             ::where('user_id', '=', $user_id)
            ->where('client_id', '=', $client_id)
            ->where('scopes', 'like', '%' . join(' ', $scope_set).'%')->first();

        if(is_null($consent)){
            $consents = UserConsent
                ::where('user_id', '=', $user_id)
                ->where('client_id', '=', $client_id)->get();

            foreach($consents as $aux_consent){
                // check if the requested scopes are on the former consent present
                 if(str_contains($aux_consent->scopes, $scope_set)){
                     $consent = $aux_consent;
                     break;
                 }
            }
        }
        return $consent;
    }

    /**
     * @param int $user_id
     * @param string $client_id
     * @param string $scopes
     * @return IUserConsent|void
     * @throws EntityNotFoundException
     */
    public function add($user_id, $client_id, $scopes)
    {
        $consent   = new UserConsent();
        $scope_set = explode(' ', $scopes);
        sort($scope_set);
        if (is_null(Client::find($client_id))) {
            throw new EntityNotFoundException();
        }

        $consent->client_id = $client_id;
        $consent->user_id   = $user_id;
        $consent->scopes    = join(' ', $scope_set);
        $consent->Save();
    }
}