Feature: As a user I want to be able to update my account settings

  Scenario: When I update my account settings, I expect to get a success response
    When I do a POST request to action "updateAccount" with content:
      | key                           | value         |
      | data.account_settings.api_key | valid-api-key |
    Then I expect the response code to be 200
    And I expect the response body to contain:
      | key                                                | value          |
      | data.context.0.dynamic.account                     | ARRAY,FILLED   |
      | data.context.0.dynamic.account.shops               | ARRAY,LENGTH:1 |
      | data.context.0.dynamic.carriers                    | ARRAY,LENGTH:7 |
      | data.context.0.dynamic.carriers.0.human            | PostNL         |
      | data.context.0.dynamic.carriers.0.id               | 1              |
      | data.context.0.dynamic.carriers.0.name             | postnl         |
      | data.context.0.dynamic.carriers.0.type             | main           |
      | data.context.0.dynamic.carriers.4.human            | DHL For You    |
      | data.context.0.dynamic.carriers.4.id               | 9              |
      | data.context.0.dynamic.carriers.4.name             | dhlforyou      |
      | data.context.0.dynamic.carriers.4.contractId       | 12424          |
      | data.context.0.dynamic.carriers.4.type             | custom         |
      | data.context.0.pluginSettingsView                  | ARRAY,FILLED   |
      | data.context.0.pluginSettingsView.carrier.children | ARRAY,LENGTH:7 |
    And I expect the API key to be marked as valid

  Scenario: When API key is invalid, I expect to get an error
    When I do a POST request to action "updateAccount" with content:
      | key                           | value           |
      | data.account_settings.api_key | invalid-api-key |
    Then I expect the response code to be 401
    And I expect the response body to contain:
      | key             | value          |
      | errors.0.status | 401            |
      | errors.0.code   | 3000           |
      | errors.0.title  | Access Denied. |
    And I expect the API key to be marked as invalid

  Scenario: When I update my account without any data, I expect account data to be fetched from the API
    Given my account is set up with 2 shops
    # This corresponds to the "manual update" button in the frontend settings
    When I do a POST request to action "updateAccount" with content:
      | key | value |
      |     |       |
    Then I expect the response code to be 200
    And I expect my account to have 1 shop
