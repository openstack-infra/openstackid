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
use OAuth2\Models\IClientPublicKey;
use OAuth2\Models\IClient;
use jwk\impl\RSAJWKFactory ;
use jwk\impl\RSAJWKPEMPublicKeySpecification;
use jwk\IJWK;
/**
 * Class ClientPublicKey
 */
final class ClientPublicKey extends AsymmetricKey implements IClientPublicKey
{

    /**
     * @return IClient
     */
    public function getOwner()
    {
        return $this->belongsTo('Models\OAuth2\Client');
    }

    /**
     * @param string $kid
     * @param string $type
     * @param string $use
     * @param string $pem
     * @param string $alg
     * @param bool $active
     * @param \DateTime $valid_from
     * @param \DateTime $valid_to
     * @return IClientPublicKey
     */
    static public function buildFromPEM($kid, $type, $use, $pem, $alg, $active, \DateTime $valid_from, \DateTime $valid_to)
    {
        $pk = new self;
        $pk->kid = $kid;
        $pk->pem_content = $pem;
        $pk->type = $type;
        $pk->usage = $use;
        $pk->alg   = $alg;
        $pk->active = $active;
        $pk->valid_from = $valid_from;
        $pk->valid_to = $valid_to;
        return $pk;
    }

    public function getPublicKeyPEM()
    {
       return $this->pem_content;
    }

    /**
     * @return IJWK
     */
    public function toJWK()
    {
        $jwk = RSAJWKFactory::build
        (
            new RSAJWKPEMPublicKeySpecification
            (
                $this->getPublicKeyPEM(),
                $this->alg
            )
        );

        $jwk->setId($this->kid);
        $jwk->setType($this->type);
        $jwk->setKeyUse($this->usage);
        return $jwk;
    }
}