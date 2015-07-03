<?php
/**
 * Server Configuration
 *
 */
return array(
    //general default values
    'Assets_Url' => 'http://www.openstack.org/',
    'MaxFailed_Login_Attempts' => 10,
    'MaxFailed_LoginAttempts_2ShowCaptcha' => 3,
    //openid default values
    'OpenId_Private_Association_Lifetime' => 240,
    'OpenId_Session_Association_Lifetime' => 21600,
    'OpenId_Nonce_Lifetime' => 360,
    /**
     * Security Policies Configuration
     */
    'BlacklistSecurityPolicy_BannedIpLifeTimeSeconds' => 21600,
    'BlacklistSecurityPolicy_MinutesWithoutExceptions' => 5,
    'BlacklistSecurityPolicy_MaxReplayAttackExceptionAttempts' => 3,
    'BlacklistSecurityPolicy_ReplayAttackExceptionInitialDelay' => 10,
    'BlacklistSecurityPolicy_MaxInvalidNonceAttempts' => 10,
    'BlacklistSecurityPolicy_InvalidNonceInitialDelay' => 10,
    'BlacklistSecurityPolicy_MaxInvalidOpenIdMessageExceptionAttempts' => 10,
    'BlacklistSecurityPolicy_InvalidOpenIdMessageExceptionInitialDelay' => 10,
    'BlacklistSecurityPolicy_MaxOpenIdInvalidRealmExceptionAttempts' => 10,
    'BlacklistSecurityPolicy_OpenIdInvalidRealmExceptionInitialDelay' => 10,
    'BlacklistSecurityPolicy_MaxInvalidOpenIdMessageModeAttempts' => 10,
    'BlacklistSecurityPolicy_InvalidOpenIdMessageModeInitialDelay' => 10,
    'BlacklistSecurityPolicy_MaxInvalidOpenIdAuthenticationRequestModeAttempts' => 10,
    'BlacklistSecurityPolicy_InvalidOpenIdAuthenticationRequestModeInitialDelay' => 10,
    'BlacklistSecurityPolicy_MaxAuthenticationExceptionAttempts' => 10,
    'BlacklistSecurityPolicy_AuthenticationExceptionInitialDelay' => 20,
    'BlacklistSecurityPolicy_MaxInvalidAssociationAttempts' => 10,
    'BlacklistSecurityPolicy_InvalidAssociationInitialDelay' => 20,
    'BlacklistSecurityPolicy_OAuth2_MaxAuthCodeReplayAttackAttempts' => 3,
    'BlacklistSecurityPolicy_OAuth2_AuthCodeReplayAttackInitialDelay' => 10,
    'BlacklistSecurityPolicy_OAuth2_MaxInvalidAuthorizationCodeAttempts' => 3,
    'BlacklistSecurityPolicy_OAuth2_InvalidAuthorizationCodeInitialDelay' => 10,
    'BlacklistSecurityPolicy_OAuth2_MaxInvalidBearerTokenDisclosureAttempt' => 3,
    'BlacklistSecurityPolicy_OAuth2_BearerTokenDisclosureAttemptInitialDelay' => 10,
    //oauth2 default config values
    'OAuth2_AuthorizationCode_Lifetime' => 240,
    'OAuth2_AccessToken_Lifetime' => 3600,
    // in seconds , should be equal to session.lifetime (120 minutes)
    'OAuth2_IdToken_Lifetime' => 7200,
    'OAuth2_RefreshToken_Lifetime' => 0,
    'OAuth2_Enable' => true,
    //oauth2 security policy configuration
    'OAuth2SecurityPolicy_MinutesWithoutExceptions' => 2,
    'OAuth2SecurityPolicy_MaxBearerTokenDisclosureAttempts' => 5,
    'OAuth2SecurityPolicy_MaxInvalidClientExceptionAttempts' => 10,
    'OAuth2SecurityPolicy_MaxInvalidRedeemAuthCodeAttempts' => 10,
    'OAuth2SecurityPolicy_MaxInvalidInvalidClientCredentialsAttempts' => 5,
);