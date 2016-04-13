<?php namespace OpenId\Handlers\Factories;
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
use OpenId\Handlers\Strategies\Implementations\SessionAssociationDHStrategy;
use OpenId\Handlers\Strategies\Implementations\SessionAssociationUnencryptedStrategy;
use OpenId\OpenIdMessage;
use OpenId\Requests\OpenIdAssociationSessionRequest;
use OpenId\Requests\OpenIdDHAssociationSessionRequest;
use OpenId\Requests\OpenIdRequest;
use OpenId\Responses\OpenIdResponse;
use OpenId\Services\OpenIdServiceCatalog;
use Utils\Services\ServiceLocator;
use Utils\Services\UtilsServiceCatalog;
/**
 * Class SessionAssociationRequestFactory
 * @package OpenId\Handlers\Factories
 */
final class SessionAssociationRequestFactory
{
    /**
     * @param OpenIdMessage $message
     * @return OpenIdRequest
     */
    public static function buildRequest(OpenIdMessage $message)
    {
        if (OpenIdDHAssociationSessionRequest::IsOpenIdDHAssociationSessionRequest($message))
            return new OpenIdDHAssociationSessionRequest($message);
        return new OpenIdAssociationSessionRequest($message);
    }

    /**
     * @param OpenIdMessage $message
     * @return OpenIdResponse
     */
    public static function buildSessionAssociationStrategy(OpenIdMessage $message) {

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