<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/28/13
 * Time: 6:17 PM
 */

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
        if (OpenIdDHAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message))
            return new OpenIdDHAssociationSessionRequest($message);
        return OpenIdAssociationSessionRequest($message);
    }

    /**
     * @param OpenIdAssociationSessionRequest $message
     * @return null|ISessionAssociationStrategy
     */
    public static function buildSessionAssociationStrategy(OpenIdMessage $message)
    {
        if (OpenIdDHAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message))
            return new SessionAssociationDHStrategy(new OpenIdDHAssociationSessionRequest($message));
        if (OpenIdAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message))
            return new SessionAssociationUnencryptedStrategy(new OpenIdAssociationSessionRequest($message));
        return null;
    }
} 