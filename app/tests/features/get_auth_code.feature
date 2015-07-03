Feature: Get OAuth2 Auth Code

  Background:
    Given Prepare For Tests is Done

  Scenario: Get Auth Code With Login
    Given these OAuth2 parameters:
      | param         |  value                                            |
      | client_id     | Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client |
      | redirect_uri  | https://www.test.com/oauth2                       |
      | response_type | code                                              |
      | scope         | profile                                           |
    And exits client Id "Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client"
    And navigate to controller action "OAuth2ProviderController@authorize" with HTTP method "POST"
    When i get log as user "test@test.com" using password "1qaz2wsx"
    And allow consent "AllowOnce"
    Then i get a valid Auth code
