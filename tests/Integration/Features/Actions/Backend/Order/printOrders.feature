Feature: As a user I want to be able to print an order

  Background:
    Given a valid API key is set

  Scenario: Print order with shipment
    Given an order with id 34 exists
    And order 34 has 1 shipment with:
      | key | value  |
      | id  | 112700 |
    When I do a GET request to action "printOrders" with parameters "orderIds=34"
    Then I expect the response code to be 200
    And I expect the response body to contain:
      | key            | value            |
      | data.pdfs.data | STRING,LENGTH:92 |
