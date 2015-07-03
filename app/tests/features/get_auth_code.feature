Feature: Get OAuth2 Auth Code

  Background:
    Given Prepare For Tests is Done

  Scenario: Get Auth Code With Login
    Given I am on "https://local.openstackid.openstack.org/oauth2/auth?scope=profile&redirect_uri=https://localhost.com&response_type=code&client_id=Jiz87D8/Vcvr6fvQbH4HyNgwTlfSyQ3x.openstack.client&access_type=offline"
    Then I should see "Search results"