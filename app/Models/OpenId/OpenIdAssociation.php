<?php namespace Models\OpenId;
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
use OpenId\Models\IAssociation;
use Utils\Model\BaseModelEloquent;
use DateTime;
use DateTimeZone;
use DateInterval;
/**
 * Class OpenIdAssociation
 * @package Models\OpenId
 */
class OpenIdAssociation extends BaseModelEloquent implements IAssociation
{

    public $timestamps = false;

    protected $table = 'openid_associations';

    /**
     * @return string
     */
    public function getMacFunction()
    {
        return $this->mac_function;
    }

    public function setMacFunction($mac_function)
    {
        // TODO: Implement setMacFunction() method.
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    public function setSecret($secret)
    {
        // TODO: Implement setSecret() method.
    }

    public function getLifetime()
    {
        return intval($this->lifetime);
    }

    public function setLifetime($lifetime)
    {
        // TODO: Implement setLifetime() method.
    }

    public function getIssued()
    {
        return $this->issued;
    }

    public function setIssued($issued)
    {
        // TODO: Implement setIssued() method.
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        // TODO: Implement setType() method.
    }

    public function IsExpired()
    {
        // TODO: Implement IsExpired() method.
    }

    public function getRealm()
    {
        return $this->realm;
    }

    public function setRealm($realm)
    {
        // TODO: Implement setRealm() method.
    }

    public function getRemainingLifetime()
    {
        $created_at = new DateTime($this->issued, new DateTimeZone("UTC"));
        $created_at->add(new DateInterval('PT' . intval($this->lifetime) . 'S'));
        $now        = new DateTime(gmdate("Y-m-d H:i:s", time()), new DateTimeZone("UTC"));
        //check validity...
        if ($now > $created_at)
            return -1;
        $seconds = abs($created_at->getTimestamp() - $now->getTimestamp());;
        return $seconds;
    }

	public function getHandle()
	{
		return $this->identifier;
	}
}