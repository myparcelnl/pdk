Feature: As a user I want to retrieve context for use with the frontend

  Scenario: Fetch data from a MyParcel account
    Given a valid API key is set
    When I do a GET request to action "fetchContext" with parameters "context=dynamic"
    Then I expect the response code to be 200
    And I expect the response body to contain:
      | key                                              | value              |
      | data.context                                     | ARRAY,LENGTH:1     |
      | data.context.0.dynamic.account.shops             | ARRAY,LENGTH:1     |
      | data.context.0.dynamic.account.shops.0.accountId | CURRENT_ACCOUNT_ID |
      | data.context.0.dynamic.account.shops.0.name      | CURRENT_SHOP_NAME  |

  Scenario: Fetch data with invalid api key
    Given an invalid API key is set
    When I do a GET request to action "fetchContext" with parameters "context=dynamic"
    Then I expect the response code to be 401
    And I expect the API key to be marked as invalid
