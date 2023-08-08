Feature: As a user I want to be able to delete my account settings

  Scenario: Delete api key
    Given a valid API key is set
    When I do a POST request to action "deleteAccount"
    Then I expect the response code to be 200
    And I expect the response body to contain:
      | key                                                  | value |
      | data.context.0.account                               | NULL  |
      | data.context.0.dynamic.pluginSettings.account.apiKey | NULL  |
