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

use Auth\User;
use Utils\Model\BaseModelEloquent;
use DateInterval;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * Class RefreshToken
 * Refresh Token Entity
 */
class RefreshToken extends BaseModelEloquent {

    protected $table = 'oauth2_refresh_token';

    private $friendly_scopes;

    protected $fillable = ['value', 'from_ip', 'lifetime','scope','audience','void','created_at','updated_at','client_id'];

    public function access_tokens()
    {
        return $this->hasMany('Models\OAuth2\AccessToken');
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
        if(intval($this->lifetime) == 0) return false;
        //check lifetime...
        $created_at = $this->created_at;
        $created_at->add(new DateInterval('PT' . intval($this->lifetime) . 'S'));
        $now        = new DateTime(gmdate("Y-m-d H:i:s", time()), new DateTimeZone("UTC"));
        return ($now > $created_at);
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

    public function getFriendlyScopes(){
        return $this->friendly_scopes;
    }

    public function setFriendlyScopes($friendly_scopes){
        $this->friendly_scopes = $friendly_scopes;
    }

    public function setVoid(){
        $this->void = true;
    }
} 