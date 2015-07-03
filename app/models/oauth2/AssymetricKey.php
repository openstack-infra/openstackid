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
     * @return string
     */
    public function getKeyId()
    {
        return $this->kid;
    }

    private function calculateThumbprint($alg){
        $pem = str_replace( array("\n","\r"), '', trim($this->getPublicKeyPEM()));
        return strtoupper(hash($alg, base64_decode($pem)));
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

}