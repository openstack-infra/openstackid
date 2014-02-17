<?php

namespace openid\handlers\factories;

use openid\handlers\strategies\implementations\SessionAssociationDHStrategy;
use openid\handlers\strategies\implementations\SessionAssociationUnencryptedStrategy;
use openid\handlers\strategies\ISessionAssociationStrategy;
use openid\OpenIdMessage;
use openid\requests\OpenIdAssociationSessionRequest;
use openid\requests\OpenIdDHAssociationSessionRequest;
use openid\services\OpenIdServiceCatalog;
use utils\services\ServiceLocator;
use utils\services\UtilsServiceCatalog;

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

	    $association_service    = ServiceLocator::getInstance()->getService(OpenIdServiceCatalog::AssociationService);
	    $configuration_service  = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::ServerConfigurationService);
	    $log_service            = ServiceLocator::getInstance()->getService(UtilsServiceCatalog::LogService);

        if (OpenIdDHAssociationSessionRequest::IsOpenIdDHAssociationSessionRequest($message))
            return new SessionAssociationDHStrategy(new OpenIdDHAssociationSessionRequest($message),$association_service,$configuration_service,$log_service);
        if (OpenIdAssociationSessionRequest::IsOpenIdAssociationSessionRequest($message))
            return new SessionAssociationUnencryptedStrategy(new OpenIdAssociationSessionRequest($message),$association_service,$configuration_service,$log_service);
        return null;
    }
} 