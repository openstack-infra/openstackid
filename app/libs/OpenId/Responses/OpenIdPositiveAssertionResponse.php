<?php namespace OpenId\Responses;
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
use OpenId\OpenIdProtocol;
/**
 * Class OpenIdPositiveAssertionResponse
 * @package OpenId\Responses
 */
class OpenIdPositiveAssertionResponse extends OpenIdIndirectResponse
{

    /**
     * OpenIdPositiveAssertionResponse constructor.
     * @param string $op_endpoint
     * @param string $claimed_id
     * @param string $identity
     * @param string $return_to
     * @param string $nonce
     * @param string $realm
     */
    public function __construct($op_endpoint, $claimed_id, $identity, $return_to, $nonce, $realm)
    {
        parent::__construct();
        $this->setMode(OpenIdProtocol::IdMode);
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_OpEndpoint)] = $op_endpoint;
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ClaimedId)] = $claimed_id;
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Identity)] = $identity;
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_ReturnTo)] = $return_to;
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Nonce)] = $nonce;
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Realm)] = $realm;
    }

    /**
     * @param string $assoc_handle
     * @return $this
     */
    public function setAssocHandle($assoc_handle)
    {
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_AssocHandle)] = $assoc_handle;
        return $this;
    }

    /**
     * @param string $signed
     * @return $this
     */
    public function setSigned($signed)
    {
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Signed)] = $signed;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSig()
    {
        return $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)];
    }

    /**
     * @param string $sig
     * @return $this
     */
    public function setSig($sig)
    {
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_Sig)] = $sig;
        return $this;
    }

    /**
     * @param string $invalidate_handle
     * @return $this
     */
    public function setInvalidateHandle($invalidate_handle)
    {
        $this[OpenIdProtocol::param(OpenIdProtocol::OpenIDProtocol_InvalidateHandle)] = $invalidate_handle;
        return $this;
    }

}
