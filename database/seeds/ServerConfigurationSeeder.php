<?php

use Models\ServerConfiguration;
use Illuminate\Database\Seeder;

/**
 * Class ServerConfigurationSeeder
 */
class ServerConfigurationSeeder extends Seeder {

    public function run()
    {
        DB::table('server_configuration')->delete();

        ServerConfiguration::create(
            array(
                'key'   => 'Private.Association.Lifetime',
                'value' => '240',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'Session.Association.Lifetime',
                'value' => '21600',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'MaxFailed.Login.Attempts',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'MaxFailed.LoginAttempts.2ShowCaptcha',
                'value' => '3',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'Nonce.Lifetime',
                'value' => '360',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'Assets.Url',
                'value' => 'http://www.openstack.org/',
            )
        );

        //blacklist policy config values

        ServerConfiguration::create(
            array(
                'key'   => 'BannedIpLifeTimeSeconds',
                'value' => '21600',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MinutesWithoutExceptions',
                'value' => '5',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.ReplayAttackExceptionInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MaxInvalidNonceAttempts',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.InvalidNonceInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MaxInvalidOpenIdMessageExceptionAttempts',
                'value' => '10',
            )
        );


        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.InvalidOpenIdMessageExceptionInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MaxOpenIdInvalidRealmExceptionAttempts',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.OpenIdInvalidRealmExceptionInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MaxInvalidOpenIdMessageModeAttempts',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.InvalidOpenIdMessageModeInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MaxInvalidOpenIdAuthenticationRequestModeAttempts',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.InvalidOpenIdAuthenticationRequestModeInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.MaxAuthenticationExceptionAttempts',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.AuthenticationExceptionInitialDelay',
                'value' => '20',
            )
        );


        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.OAuth2.MaxAuthCodeReplayAttackAttempts',
                'value' => '3',
            )
        );


        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.OAuth2.AuthCodeReplayAttackInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.OAuth2.MaxInvalidAuthorizationCodeAttempts',
                'value' => '3',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.OAuth2.InvalidAuthorizationCodeInitialDelay',
                'value' => '10',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.OAuth2.MaxInvalidBearerTokenDisclosureAttempt',
                'value' => '3',
            )
        );

        ServerConfiguration::create(
            array(
                'key'   => 'BlacklistSecurityPolicy.OAuth2.BearerTokenDisclosureAttemptInitialDelay',
                'value' => '10',
            )
        );
    }

} 