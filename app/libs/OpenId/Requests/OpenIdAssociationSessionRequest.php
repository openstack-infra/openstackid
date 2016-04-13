<?php namespace OpenId\Requests;
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
use OpenId\Exceptions\InvalidAssociationTypeException;
use OpenId\Exceptions\InvalidSessionTypeException;
use OpenId\Helpers\OpenIdErrorMessages;
use OpenId\OpenIdMessage;
use OpenId\OpenIdProtocol;
/**
 * Class OpenIdAssociationSessionRequest
 * @package OpenId\Requests
 */
class OpenIdAssociationSessionRequest extends OpenIdRequest {

    /**
     * OpenIdAssociationSessionRequest constructor.
     * @param OpenIdMessage $message
     */
    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
    }

    /**
     * @param OpenIdMessage $message
     * @return bool
     */
    public static function IsOpenIdAssociationSessionRequest(OpenIdMessage $message)
    {
        $mode = $message->getMode();
        if ($mode == OpenIdProtocol::AssociateMode) return true;
        return false;
    }

    /**
     * @return bool
     * @throws InvalidSessionTypeException
     * @throws InvalidAssociationTypeException
     */
    public function isValid()
    {
        $mode = $this->getMode();
        if ($mode != OpenIdProtocol::AssociateMode)
            return false;

        $assoc_type = $this->getAssocType();

        if (is_null($assoc_type) || empty($assoc_type))
            return false;

        $session_type = $this->getSessionType();

        if (is_null($session_type) || empty($session_type))
            return false;

        if (!OpenIdProtocol::isSessionTypeSupported($session_type))
            throw new InvalidSessionTypeException(sprintf(OpenIdErrorMessages::UnsupportedSessionTypeMessage, $session_type));

        if (!OpenIdProtocol::isAssocTypeSupported($assoc_type))
            throw new InvalidAssociationTypeException(sprintf(OpenIdErrorMessages::UnsupportedAssociationTypeMessage, $assoc_type));

        return true;
    }

    /**
     * @return string
     */
    public function getAssocType()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_AssocType);
    }

    /**
     * @return string
     */
    public function getSessionType()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_SessionType);
    }
}