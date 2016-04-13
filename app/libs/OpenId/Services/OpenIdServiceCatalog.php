<?php namespace OpenId\Services;
/**
 * Class OpenIdServiceCatalog
 * @package OpenId\Services;
 */
abstract class OpenIdServiceCatalog
{
    const MementoSerializerService   = \OpenId\Services\IMementoOpenIdSerializerService::class;
    const AuthenticationStrategy     = \OpenId\Handlers\IOpenIdAuthenticationStrategy::class;
    const ServerExtensionsService    = \OpenId\Services\IServerExtensionsService::class;
    const AssociationService         = \OpenId\Services\IAssociationService::class;
    const TrustedSitesService        = \OpenId\Services\ITrustedSitesService::class;
    const ServerConfigurationService = \OpenId\Services\IServerConfigurationService::class;
    const UserService                = \OpenId\Services\IUserService::class;
    const NonceService               = \OpenId\Services\INonceService::class;
}