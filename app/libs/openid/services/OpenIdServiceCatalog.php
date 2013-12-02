<?php

namespace openid\services;


class OpenIdServiceCatalog
{
    const MementoService = 'openid\\services\\IMementoOpenIdRequestService';
    const AuthenticationStrategy = 'openid\\handlers\\IOpenIdAuthenticationStrategy';
    const ServerExtensionsService = 'openid\\services\\IServerExtensionsService';
    const AssociationService = 'openid\\services\\IAssociationService';
    const TrustedSitesService = 'openid\\services\\ITrustedSitesService';
    const ServerConfigurationService = 'openid\\services\\IServerConfigurationService';
    const UserService = 'openid\\services\\IUserService';
    const NonceService = 'openid\\services\\INonceService';
    const LogService = 'openid\\services\\ILogService';
    const AuthenticationService = 'openid\\services\\IAuthService';
    const CheckPointService = 'openid\\services\\ICheckPointService';
}
