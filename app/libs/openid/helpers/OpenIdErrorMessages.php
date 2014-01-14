<?php
/**
 * Created by JetBrains PhpStorm.
 * User: smarcet
 * Date: 10/17/13
 * Time: 4:39 PM
 * To change this template use File | Settings | File Templates.
 */

namespace openid\helpers;

class OpenIdErrorMessages
{
    const RealmNotAllowedByUserMessage                   = "Realm %s is not authorized by user";
    const UnsupportedAssociationTypeMessage              = "Unsupported assoc type %s";
    const UnsupportedSessionTypeMessage                  = "Unsupported session type %s";
    const InvalidAssociationTypeMessage                  = "Invalid association type requested.";
    const InvalidDHParamMessage                          = "Invalid %s param.";
    const InvalidKVFormatChar                            = "Key %s has invalid char ('%s')";
    const InvalidNonceFormatMessage                      = "Invalid Nonce Format %s";
    const InvalidNonceTimestampMessage                   = "Invalid Nonce timestamp %s";
    const InvalidAuthenticationRequestModeMessage        = "Invalid Mode %s";
    const InvalidOpenIdAuthenticationRequestMessage      = 'OpenId Authentication Request is Invalid!';
    const AXInvalidModeMessage                           = 'AX: not set or invalid mode';
    const AXInvalidRequiredAttributesMessage             = 'AX: not set required attributes!';
    const AXInvalidNamespaceMessage                      = 'AX: invalid ns for attribute %s';
    const InvalidOpenIdCheckAuthenticationRequestMessage = 'OpenId Check Authentication Request is Invalid!';
    const ReplayAttackNonceAlreadyUsed                   = 'Nonce %s already used on a formed request!';
    const ReplayAttackNonceAlreadyEmittedForAnotherRealm = "Nonce %s was not emit for Realm!";
    const ReplayAttackPrivateAssociationAlreadyUsed      = "Private Association %s already used";
    const UnhandledMessage                               = "Unhandled message %s";
    const InvalidOpenIdMessage                           = "There is not a valid OpenIdMessage set on request";
    const InvalidAssociationSessionRequest               = "Association Session Request is Invalid!";
    const InvalidOpenIdMessageModeMessage                = 'Invalid %s Mode';
    const InvalidMacFunctionMessage                      = "Invalid mac function %s";
    const InvalidPrivateAssociationMessage               = "Private Association %s was not emit for requested realm %s";
    const AlreadyExistSessionMessage                     = "There is a current session with identity %s, but user wants to use a different identity %s";
    const OAuth2MissingRequiredParam                     = 'OAuth2 OpenId Extension: missing required field %s';
}