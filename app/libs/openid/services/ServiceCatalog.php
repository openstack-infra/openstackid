<?php
/**
 * Created by PhpStorm.
 * User: smarcet
 * Date: 10/26/13
 * Time: 5:25 PM
 */

namespace openid\services;


class ServiceCatalog {
    const MementoService             = 'openid\\services\\IMementoOpenIdRequestService';
    const AuthenticationStrategy     = 'openid\\handlers\\IOpenIdAuthenticationStrategy';
    const ServerExtensionsService    = 'openid\\services\\IServerExtensionsService';
    const AssociationService         = 'openid\\services\\IAssociationService';
    const TrustedSitesService        = 'openid\\services\\ITrustedSitesService';
    const ServerConfigurationService = 'openid\\services\\IServerConfigurationService';
    const UserService                = 'openid\\services\\IUserService';
    const NonceService               = 'openid\\services\\INonceService';
    const LogService                 = 'openid\\services\\ILogService';
    const AuthenticationService      = 'openid\\services\\IAuthService';
}
