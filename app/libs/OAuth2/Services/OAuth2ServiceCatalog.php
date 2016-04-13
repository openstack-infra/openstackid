<?php namespace OAuth2\Services;
/**
 * Class OAuth2ServiceCatalog
 * @package OAuth2\Services
 */
abstract class OAuth2ServiceCatalog
{
    const TokenService                       = \OAuth2\Services\ITokenService::class;
    const ClientCredentialGenerator          = \OAuth2\Services\IClientCredentialGenerator::class;
    const ClientService                      = \OAuth2\Services\IClientService::class;
    const ScopeService                       = \OAuth2\Services\IApiScopeService::class;
    const ResourceServerService              = \OAuth2\Services\IResourceServerService::class;
    const ApiService                         = \OAuth2\Services\IApiService::class;
    const ApiEndpointService                 = \OAuth2\Services\IApiEndpointService::class;
    const UserConsentService                 = \OAuth2\Services\IUserConsentService::class;
    const ClientPublicKeyService             = \OAuth2\Services\IClientPublicKeyService::class;
    const ServerPrivateKeyService            = \OAuth2\Services\IServerPrivateKeyService::class;
    const OpenIDProviderConfigurationService = \OAuth2\Discovery\IOpenIDProviderConfigurationService::class;
    const MementoSerializerService           = \OAuth2\Services\IMementoOAuth2SerializerService::class;
    const AuthenticationStrategy             = \OAuth2\Strategies\IOAuth2AuthenticationStrategy::class;
    const PrincipalService                   = \OAuth2\Services\IPrincipalService::class;
    const SecurityContextService             = \OAuth2\Services\ISecurityContextService::class;
}