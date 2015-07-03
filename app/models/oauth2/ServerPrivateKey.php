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
use oauth2\models\IServerPrivateKey;

/**
 * Class ServerPrivateKey
 */
final class ServerPrivateKey extends AssymetricKey implements IServerPrivateKey
{
    /**
     * @var \Crypt_AES()
     */
    private $aes;

    /**
     * @var string
     */

    private $enc_key;

    /**
     * @var string
     */
    private $iv;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->aes = new \Crypt_AES();
        $this->aes->Crypt_Base(CRYPT_AES_MODE_CBC);

        $this->enc_key = Config::get('privatekey.EncryptionKey');
        if(empty($this->enc_key)) throw new \InvalidArgumentException('privatekey.EncryptionKey not set!');
        $this->iv      = Config::get('privatekey.IV');
        if(empty($this->iv)) throw new \InvalidArgumentException('privatekey.IV not set!');
    }

    /**
     * @param string $value
     * @return String
     */
    private function encrypt($value){
        $this->aes->setKey($this->enc_key);
        $this->aes->setIV($this->iv);
        return base64_encode($this->aes->encrypt($value));
    }

    /**
     * @param string $value
     * @return String
     */
    private function decrypt($value){
        $value = base64_decode($value);
        $this->aes->setKey($this->enc_key);
        $this->aes->setIV($this->iv);
        return $this->aes->decrypt($value);
    }


    public function getPemContentAttribute(){
        return $this->decrypt($this->attributes['pem_content']);
    }

    public function setPemContentAttribute($value){

        $this->attributes['pem_content'] = $this->encrypt($value);
    }

    public function getPasswordAttribute(){
        return $this->decrypt($this->attributes['password']);
    }

    public function setPasswordAttribute($value){

        $this->attributes['password'] = $this->encrypt($value);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $kid
     * @param \DateTime $valid_from
     * @param \DateTime $valid_to
     * @param string $type
     * @param string $use
     * @param bool $active
     * @param string $pem_content
     * @param null|string $password
     * @return IServerPrivateKey
     */
    static function build($kid, \DateTime $valid_from, \DateTime $valid_to, $type, $use, $active, $pem_content, $password = null)
    {
        $key = new self;
        $key->kid         = $kid;
        $key->valid_from  = $valid_from;
        $key->valid_to    = $valid_to;
        $key->type        = $type;
        $key->usage       = $use;
        $key->active      = $active;
        $key->pem_content = $pem_content;
        $key->password    = $password;
        return $key;
    }

    public function getPublicKeyPEM()
    {
        $private_key_pem =  $this->pem_content;
        $rsa             = new Crypt_RSA();

        if(!empty($this->password)){
            $rsa->setPassword($this->password);
        }

        $rsa->loadKey($private_key_pem);
        return $rsa->getPublicKey();
    }
}