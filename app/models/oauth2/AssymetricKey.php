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

use utils\model\BaseModelEloquent;
use oauth2\models\IAssymetricKey;
use jwa\cryptographic_algorithms\ICryptoAlgorithm;
use jwa\cryptographic_algorithms\KeyManagementAlgorithms_Registry;
use jwa\cryptographic_algorithms\DigitalSignatures_MACs_Registry;
/**
 * Class AssymetricKey
 */
abstract class AssymetricKey extends BaseModelEloquent implements IAssymetricKey
{

    protected $table = 'oauth2_assymetric_keys';

    protected $stiClassField = 'class_name';

    protected $stiBaseClass = 'AssymetricKey';

    protected $fillable = array(
        'kid',
        'pem_content',
        'active',
        'usage',
        'type',
        'last_use',
        'valid_from',
        'valid_to',
        'alg'
    );

    /**
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getUse()
    {
        return $this->usage;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->active;
    }

    /**
     * @return \DateTime
     */
    public function getLastUse()
    {
        return $this->last_use;
    }

    /**
     * @return $this
     */
    public function markAsUsed()
    {
        $this->last_use = new DateTime();
        return $this;
    }

    /**
     * @return string
     */
    public function getKeyId()
    {
        return $this->kid;
    }

    private function calculateThumbprint($alg)
    {
        $res = '';
        try {
            $pem = str_replace(array("\n", "\r"), '', trim($this->getPublicKeyPEM()));
            $res = strtoupper(hash($alg, base64_decode($pem)));
        }
        catch(Exception $ex)
        {
            $res = 'INVALID';
        }
        return $res;
    }

    /**
     * @return string
     */
    public function getSHA_1_Thumbprint()
    {
        return $this->calculateThumbprint('sha1');
    }

    /**
     * @return string
     */
    public function getSHA_256_Thumbprint()
    {
        return $this->calculateThumbprint('sha256');
    }

    abstract public function getPublicKeyPEM();

    /**
     * @return string
     */
    public function getPEM()
    {
        return $this->pem_content;
    }

    /**
     * checks validatiry range with now
     * @return bool
     */
    public function isExpired()
    {
        $now = new \DateTime();
        return ( $this->valid_from <= $now && $this->valid_to >= $now);
    }

    /**
     * algorithm intended for use with the key
     * @return ICryptoAlgorithm
     */
    public function getAlg()
    {
        $algorithm = DigitalSignatures_MACs_Registry::getInstance()->get($this->alg);

        if(is_null($algorithm))
        {
            $algorithm = KeyManagementAlgorithms_Registry::getInstance()->get($this->alg);
        }
        return $algorithm;
    }

}