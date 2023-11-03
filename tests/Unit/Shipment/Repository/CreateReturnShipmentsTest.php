<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Repository;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesEachMockPdkInstance());

const INPUT_RECIPIENT = [
    'cc'         => 'NL',
    'city'       => 'Hoofddorp',
    'email'      => 'test@myparcel.nl',
    'person'     => 'Jaap Krekel',
    'postalCode' => '2132JE',
    'street'     => 'Antareslaan 31',
];

const DEFAULT_INPUT_SENDER_SHIPMENT_RETURN = [
    'cc'         => 'NL',
    'city'       => 'Amsterdam',
    'number'     => '2',
    'person'     => 'Willem Wever',
    'postalCode' => '4164ZF',
    'street'     => 'Werf',
];

it('creates return shipment', function (array $input) {
    MockApi::enqueue(new ExamplePostIdsResponse(), new ExampleGetShipmentsResponse());

    $repository             = Pdk::get(ShipmentRepository::class);
    $inputShipments         = (new Collection($input))->mapInto(Shipment::class);
    $createdReturnShipments = $repository->createReturnShipments(new ShipmentCollection($inputShipments->all()));
    $array                  = $createdReturnShipments->toArrayWithoutNull();

    foreach ($array as $index => $shipment) {
        unset($shipment['carrier']['capabilities'], $shipment['carrier']['returnCapabilities']);
        $array[$index] = $shipment;
    }

    expect($createdReturnShipments)
        ->toBeInstanceOf(ShipmentCollection::class);

    assertMatchesJsonSnapshot(json_encode($array));
})->with([
    'simple domestic shipment' => [
        'input' => [
            [
                'id'                  => 65435213,
                'carrier'             => ['id' => Carrier::CARRIER_POSTNL_ID],
                'deliveryOptions'     => [
                    'date'            => '2022-07-10 16:00:00',
                    'shipmentOptions' => [
                        'ageCheck'         => true,
                        'insurance'        => 500,
                        'labelDescription' => 'order 204829',
                        'largeFormat'      => false,
                        'onlyRecipient'    => true,
                        'return'           => false,
                        'sameDayDelivery'  => false,
                        'signature'        => false,
                    ],
                ],
                'physicalProperties'  => [
                    'height' => 100,
                    'width'  => 120,
                    'length' => 80,
                    'weight' => 2000,
                ],
                'recipient'           => INPUT_RECIPIENT,
                'referenceIdentifier' => 'Fulfilment-1',
                'sender'              => DEFAULT_INPUT_SENDER_SHIPMENT_RETURN,
            ],
        ],
    ],
]);

it('creates a valid request from a shipment collection', function ($input, $path, $query) {
    MockApi::enqueue(new ExamplePostIdsResponse(), new ExampleGetShipmentsResponse());

    $repository = Pdk::get(ShipmentRepository::class);
    $response   = $repository->createReturnShipments(new ShipmentCollection($input));
    $request    = MockApi::ensureLastRequest();

    $uri = $request->getUri();

    expect($uri->getQuery())
        ->toBe($query)
        ->and($uri->getPath())
        ->toBe($path)
        ->and($response)
        ->toBeInstanceOf(ShipmentCollection::class);
})->with([
    'single shipment' => [
        'input' => [
            [
                'id'                  => 65435213,
                'carrier'             => ['id' => Carrier::CARRIER_POSTNL_ID],
                'deliveryOptions'     => [
                    'date'            => '2022-07-10 16:00:00',
                    'shipmentOptions' => [
                        'ageCheck'         => true,
                        'insurance'        => 500,
                        'labelDescription' => 'order 204829',
                        'largeFormat'      => false,
                        'onlyRecipient'    => true,
                        'return'           => false,
                        'sameDayDelivery'  => false,
                        'signature'        => false,
                    ],
                ],
                'physicalProperties'  => [
                    'height' => 100,
                    'width'  => 120,
                    'length' => 80,
                    'weight' => 2000,
                ],
                'recipient'           => INPUT_RECIPIENT,
                'referenceIdentifier' => 'my_ref_id',
                'sender'              => DEFAULT_INPUT_SENDER_SHIPMENT_RETURN,
            ],
        ],
        'path'  => 'API/shipments/1',
        'query' => 'link_consumer_portal=1',
    ],
]);
