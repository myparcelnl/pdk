Feature: As a user I want to be able to update my account settings

  Scenario: Marks API key as invalid when a request is done
    When I do a POST request to action "updateAccount" with content:
      | key                           | value           |
      | data.account_settings.api_key | invalid-api-key |
    Then I expect the response code to be 401
    And I expect the response body to contain:
      | key             | value          |
      | errors.0.status | 401            |
      | errors.0.code   | 3000           |
      | errors.0.title  | Access Denied. |
