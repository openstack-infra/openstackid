<?php namespace OAuth2\Models;
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
use DateInterval;
use DateTime;
use DateTimeZone;
use Utils\IPHelper;
use Utils\Model\Identifier;
/**
 * Class Token
 * Defines the common behavior for all emitted tokens
 * @package OAuth2\Models
 */
abstract class Token extends Identifier
{

    const DefaultByteLength = 32;
    /**
     * @var string
     */
    protected $issued;
    /**
     * oauth2 client id
     * @var string
     */
    protected $client_id;
    /**
     * @var int
     */
    protected $len;
    /**
     * @var string
     */
    protected $scope;
    /**
     * @var string
     */
    protected $audience;
    /**
     * @var string
     */
    protected $from_ip;
    /**
     * @var bool
     */
    protected $is_hashed;
    protected $user_id;

    public function __construct($len = self::DefaultByteLength)
    {
        parent::__construct($len);

        $this->is_hashed = false;
        $this->issued    = gmdate("Y-m-d H:i:s", time());
        $this->from_ip   = IPHelper::getUserIp();
    }

    public function getIssued()
    {
        return $this->issued;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * oauth2 client id
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @return mixed
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * @return string
     */
    public function getFromIp()
    {
        return $this->from_ip;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return intval($this->user_id);
    }

    public function getRemainingLifetime()
    {
        //check is refresh token is stills alive... (ZERO is infinite lifetime)
        if (intval($this->lifetime) == 0) {
            return 0;
        }
        $created_at = new DateTime($this->issued, new DateTimeZone("UTC"));
        $created_at->add(new DateInterval('PT' . intval($this->lifetime) . 'S'));
        $now = new DateTime(gmdate("Y-m-d H:i:s", time()), new DateTimeZone("UTC"));
        //check validity...
        if ($now > $created_at) {
            return -1;
        }
        $seconds = abs($created_at->getTimestamp() - $now->getTimestamp());;

        return $seconds;
    }

    /**
     * @return bool
     */
    public function isHashed()
    {
        return $this->is_hashed;
    }

    public abstract function toJSON();


    public abstract function fromJSON($json);
} 