<?php

namespace openid\requests;

use openid\exceptions\InvalidAssociationTypeException;
use openid\exceptions\InvalidSessionTypeException;
use openid\helpers\OpenIdErrorMessages;
use openid\OpenIdMessage;
use openid\OpenIdProtocol;

/**
 * Class OpenIdAssociationSessionRequest
 * @package openid\requests
 */
class OpenIdAssociationSessionRequest extends OpenIdRequest {


    public function __construct(OpenIdMessage $message)
    {
        parent::__construct($message);
    }

    public static function IsOpenIdAssociationSessionRequest(OpenIdMessage $message)
    {
        $mode = $message->getMode();
        if ($mode == OpenIdProtocol::AssociateMode) return true;
        return false;
    }

    /**
     * @return bool
     * @throws \openid\exceptions\InvalidSessionTypeException
     * @throws \openid\exceptions\InvalidAssociationTypeException
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

    public function getAssocType()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_AssocType);
    }

    public function getSessionType()
    {
        return $this->getParam(OpenIdProtocol::OpenIDProtocol_SessionType);
    }
}