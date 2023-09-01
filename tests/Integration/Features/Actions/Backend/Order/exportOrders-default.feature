Feature: As a user I want to be able to export an order

  Background:
    Given a valid API key is set
    And an order with id 12 exists

  Scenario: Export order to shipment
    When I do a POST request to action "exportOrders" with parameters "orderIds=12"
    Then I expect the response code to be 200
    And I expect order 12 not to be exported to MyParcel
    And I expect order 12 to have 1 shipment
    And I expect the response body to contain:
      | key                               | value          |
      | data.orders                       | array,LENGTH:1 |
      | data.orders.0.shipments           | array,LENGTH:1 |
      | data.orders.0.shipments.0.orderId | 12             |

  Scenario: Export order after changing settings in the form
    When I do a POST request to action "exportOrders" with parameters "orderIds=12" and content:
      | key                                                       | value   |
      | data.orders.0.deliveryOptions.carrier                     | postnl  |
      | data.orders.0.deliveryOptions.packageType                 | package |
      | data.orders.0.deliveryOptions.labelAmount                 | 1       |
      | data.orders.0.deliveryOptions.shipmentOptions.largeFormat | 1       |
    Then I expect the response code to be 200
    And I expect order 12 to have 1 shipment
    And I expect order 12 to have the following delivery options:
      | key                         | value   |
      | carrier.externalIdentifier  | postnl  |
      | packageType                 | package |
      | labelAmount                 | 1       |
      | shipmentOptions.largeFormat | 1       |
