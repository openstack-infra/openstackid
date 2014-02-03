<?php

namespace openid\handlers\factories;

use openid\handlers\strategies\implementations\SessionAssociationDHStrategy;
use openid\handlers\strategies\implementations\SessionAssociationUnencryptedStrategy;
use openid\handlers\strategies\ISessionAssociationStrategy;
use openid\OpenIdMessage;
use openid\requests\OpenIdAssociationSessionRequest;
use openid\requests\OpenIdDHAssociationSessionRequest;

class SessionAssociationRequestFactory
{

    public static function buildRequest(OpenIdMessage $message)
    {
        if (OpenIdDHAssociationSessionRequest::IsOpenIdDHAssociationSessionRequest($message))
            return new OpenIdDHAssociationSessionRequest($message);
        return new OpenIdAssociationSessionRequest($message);
    }

    /**
     * @param OpenIdMessage $message
     * @return null|SessionAssociationDHStrategy|SessionAssociationUnencryptedStrategy
     */
    public static function buildSessionAssociationStrategy(OpenIdMessage $message)
    {
        if (OpenIdDHAssociationSessionRequest::IsOpenIdDHAssociationSessionRequest($message))
            return new SessionAssociationDHStrategy(new OpenIdDHAssociationSessionRequest($message));
        if (OpenIdAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message))
            return new SessionAssociationUnencryptedStrategy(new OpenIdAssociationSessionRequest($message));
        return null;
    }
} 