<?php namespace Models\OAuth2;
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
use Auth\User;
use OAuth2\Models\IClient;
use Utils\Model\BaseModelEloquent;
use DateTime;
use DateInterval;
use DateTimeZone;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;

/**
 * Class AccessToken
 * @package Models\OAuth2
 */
class AccessToken extends BaseModelEloquent {

    protected $fillable = array
    (
        'value',
        'user_id',
        'from_ip',
        'associated_authorization_code',
        'lifetime',
        'scope',
        'audience',
        'created_at',
        'updated_at',
        'client_id',
        'refresh_token_id'
    );

    protected $table = 'oauth2_access_token';

    private $friendly_scopes;

    public function refresh_token()
    {
        return $this->belongsTo('Models\OAuth2\RefreshToken');
    }

    /**
     * @return RefreshToken
     */
    public function getRefreshToken(){
        return Cache::remember
        (
            'refresh_token_'.$this->refresh_token_id,
            Config::get("cache_regions.region_refresh_token_lifetime", 1140),
            function() {
                return $this->refresh_token()->first();
            }
        );
    }

    public function client(){
        return $this->belongsTo('Models\OAuth2\Client');
    }

    /**
     * @return IClient
     */
    public function getClient(){
        return Cache::remember
        (
            'client_'.$this->client_id,
            Config::get("cache_regions.region_clients_lifetime", 1140),
            function() {
                return $this->client()->first();
            }
        );
    }

    public function user(){
        return $this->belongsTo('Auth\User');
    }

    /**
     * @return User
     */
    public function getUser(){
        return Cache::remember
        (
            'user_'.$this->user_id,
            Config::get("cache_regions.region_users_lifetime", 1140),
            function() {
                return $this->user()->first();
            }
        );
    }

    /**
     * @return bool
     */
    public function isVoid(){
        //check lifetime...
        $created_at = $this->created_at;
        $created_at->add(new DateInterval('PT' . intval($this->lifetime) . 'S'));
        $now        = new DateTime(gmdate("Y-m-d H:i:s", time()), new DateTimeZone("UTC"));
        return ($now > $created_at);
    }

    /**
     * @return mixed
     */
    public function getFriendlyScopes(){
        return $this->friendly_scopes;
    }

    /**
     * @param $friendly_scopes
     */
    public function setFriendlyScopes($friendly_scopes){
        $this->friendly_scopes = $friendly_scopes;
    }

    /**
     * @return int
     */
    public function getRemainingLifetime()
    {
        //check is refresh token is stills alive... (ZERO is infinite lifetime)
        if (intval($this->lifetime) == 0) return 0;
        $created_at = new DateTime($this->created_at, new DateTimeZone("UTC"));
        $created_at->add(new DateInterval('PT' . intval($this->lifetime) . 'S'));
        $now = new DateTime(gmdate("Y-m-d H:i:s", time()), new DateTimeZone("UTC"));
        //check validity...
        if ($now > $created_at)
            return -1;
        $seconds = abs($created_at->getTimestamp() - $now->getTimestamp());;
        return $seconds;
    }
} 