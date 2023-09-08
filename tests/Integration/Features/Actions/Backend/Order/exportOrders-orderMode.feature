Feature: As a user I want to be able to export an order in order mode

  Background:
    Given a valid API key is set
    And an order with id 42 exists
    And the order setting orderMode is enabled

  Scenario: Export entire order
    When I do a POST request to action "exportOrders" with parameters "orderIds=42"
    Then I expect the response code to be 200
    And I expect order 42 to be exported to MyParcel
    And I expect order 42 to have 0 shipments
    And I expect the response body to contain:
      | key                     | value          |
      | data.orders             | array,LENGTH:1 |
      | data.orders.0.shipments | array,LENGTH:0 |
      | data.orders.0.exported  | true           |
    # Expect not to have been changed
    And I expect order 12 to have the following delivery options:
      | key                             | value |
      | shipmentOptions.ageCheck        | -1    |
      | shipmentOptions.hideSender      | -1    |
      | shipmentOptions.insurance       | -1    |
      | shipmentOptions.largeFormat     | -1    |
      | shipmentOptions.onlyRecipient   | -1    |
      | shipmentOptions.return          | -1    |
      | shipmentOptions.sameDayDelivery | -1    |
      | shipmentOptions.signature       | -1    |

  Scenario: Export order after changing settings in the form
    When I do a POST request to action "exportOrders" with parameters "orderIds=42" and content:
      | key                                                     | value     |
      | data.orders.0.deliveryOptions.carrier                   | dhlforyou |
      | data.orders.0.deliveryOptions.packageType               | package   |
      | data.orders.0.deliveryOptions.labelAmount               | 1         |
      | data.orders.0.deliveryOptions.shipmentOptions.signature | 1         |
    Then I expect the response code to be 200
    And I expect order 42 to be exported to MyParcel
    And I expect order 42 to have the following delivery options:
      | key                        | value     |
      | carrier.externalIdentifier | dhlforyou |
      | packageType                | package   |
      | labelAmount                | 1         |
      | shipmentOptions.signature  | 1         |
