Feature: As a user I want to be able to export an order

  Background:
    Given a valid API key is set

  Scenario: Export order to shipment
    Given an order with id 12 exists
    When I do a POST request to action "exportOrders" with parameters "orderIds=12"
    Then I expect the response code to be 200
    And I expect order 12 not to be exported to MyParcel
    And I expect order 12 to have 1 shipment
    And I expect the response body to contain:
      | key                               | value          |
      | data.orders                       | array,LENGTH:1 |
      | data.orders.0.shipments           | array,LENGTH:1 |
      | data.orders.0.shipments.0.orderId | 12             |
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

  Scenario: Export order to shipment that already has shipments
    Given an order with id W1S exists
    And order W1S has 1 shipment with:
      | key | value  |
      | id  | 130100 |
    When I do a POST request to action "exportOrders" with parameters "orderIds=W1S"
    Then I expect the response code to be 200
    And I expect order W1S not to be exported to MyParcel
    And I expect order W1S to have 2 shipments
    And I expect the response body to contain:
      | key                               | value          |
      | data.orders                       | array,LENGTH:1 |
      | data.orders.0.shipments           | array,LENGTH:2 |
      | data.orders.0.shipments.0.id      | 130100         |
      | data.orders.0.shipments.0.orderId | W1S            |
      | data.orders.0.shipments.1.id      | 130400         |
      | data.orders.0.shipments.1.orderId | W1S            |

  Scenario: Export order after changing settings in the form
    Given an order with id 12 exists
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
