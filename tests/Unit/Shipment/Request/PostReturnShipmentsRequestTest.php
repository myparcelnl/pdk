<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesNotificationsMock;
use RuntimeException;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesNotificationsMock());

it('creates a valid request for return shipments', function () {
    $shipmentCollection = new ShipmentCollection([
        new Shipment([
            'id' => 100020,
            'carrier' => new Carrier(['name' => 'postnl']),
            'referenceIdentifier' => 'REF-1',
            'recipient' => new ContactDetails([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'address1' => 'Main Street',
                'number' => '1',
                'postalCode' => '1234AB',
                'city' => 'Amsterdam',
                'cc' => 'NL',
                'email' => 'john@example.com',
                'person' => 'John Doe',
            ]),
            'deliveryOptions' => [
                'packageType' => 1,
            ],
        ]),
    ]);

    $request = new PostReturnShipmentsRequest($shipmentCollection);

    $body = json_decode($request->getBody(), true);

    expect($request->getMethod())
        ->toBe('POST')
        ->and($request->getPath())
        ->toBe('/shipments')
        ->and($body)
        ->toBeArray()
        ->and(Arr::get($body, 'data.return_shipments.0.parent'))
        ->toBe(100020)
        ->and(Arr::get($body, 'data.return_shipments.0.reference_identifier'))
        ->toBe('REF-1');
});

it('throws an exception when recipient data is missing', function () {
    $shipmentCollection = new ShipmentCollection([
        new Shipment([
            'id' => 100020,
            'carrier' => new Carrier(['name' => 'postnl']),
            'referenceIdentifier' => 'REF-1',
            // No recipient data
        ]),
    ]);

    $request = new PostReturnShipmentsRequest($shipmentCollection);

    // This should throw an exception
    $request->getBody();
})->throws(RuntimeException::class, 'Recipient data is required for return shipments');

it('ensures return capabilities for shipments', function () {
    $shipmentCollection = new ShipmentCollection([
        new Shipment([
            'id' => 100020,
            'carrier' => new Carrier(['name' => 'postnl']),
            'referenceIdentifier' => 'REF-1',
            'recipient' => new ContactDetails([
                'firstName' => 'John',
                'lastName' => 'Doe',
                'address1' => 'Main Street',
                'number' => '1',
                'postalCode' => '1234AB',
                'city' => 'Amsterdam',
                'cc' => 'NL',
                'email' => 'john@example.com',
                'person' => 'John Doe',
            ]),
            'deliveryOptions' => [
                'packageType' => 1,
            ],
            'isReturn' => false,
        ]),
    ]);

    $request = new PostReturnShipmentsRequest($shipmentCollection);
    $body = json_decode($request->getBody(), true);

    // Check if the shipment is properly formatted for return
    expect(Arr::get($body, 'data.return_shipments.0.options.package_type'))
        ->toBe(1) // Default package type for return shipments
        ->and(Arr::get($body, 'data.return_shipments.0.sender.cc'))
        ->toBe('NL')
        ->and(Arr::get($body, 'data.return_shipments.0.sender.person'))
        ->toBe('John Doe');
}); 