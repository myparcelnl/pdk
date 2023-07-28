Feature: As a user I want to create and get my account

  Scenario: Fetch data from a MyParcel account
    Given a valid API key is set
    When I do a GET request to action "fetchContext" with parameters "context=dynamic"
    Then I expect the response code to be 200
    And I expect the response body to contain:
      | key                                              | value              |
      | data.context                                     | ARRAY,LENGTH:1     |
      | data.context.0.dynamic.account.shops.0.accountId | CURRENT_ACCOUNT_ID |
      | data.context.0.dynamic.account.shops.0.name      | CURRENT_SHOP_NAME  |
