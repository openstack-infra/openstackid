<?php

namespace openid\services;

/**
 * Class OpenIdServiceCatalog
 * @package openid\services
 */
abstract class OpenIdServiceCatalog
{
    const MementoSerializerService = 'openid\\services\\IMementoOpenIdSerializerService';
    const AuthenticationStrategy = 'openid\\handlers\\IOpenIdAuthenticationStrategy';
    const ServerExtensionsService = 'openid\\services\\IServerExtensionsService';
    const AssociationService = 'openid\\services\\IAssociationService';
    const TrustedSitesService = 'openid\\services\\ITrustedSitesService';
    const ServerConfigurationService = 'openid\\services\\IServerConfigurationService';
    const UserService = 'openid\\services\\IUserService';
    const NonceService = 'openid\\services\\INonceService';
}