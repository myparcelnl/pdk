<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

const INPUT_RECIPIENT = [
    'cc'         => 'NL',
    'city'       => 'Hoofddorp',
    'email'      => 'test@myparcel.nl',
    'person'     => 'Jaap Krekel',
    'postalCode' => '2132JE',
    'street'     => 'Antareslaan 31',
];

const DEFAULT_INPUT_SENDER = [
    'cc'         => 'NL',
    'city'       => 'Amsterdam',
    'number'     => '2',
    'person'     => 'Willem Wever',
    'postalCode' => '4164ZF',
    'street'     => 'Werf',
];

it('creates return shipment', function (array $input) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostIdsResponse());
    $mock->append(new ExampleGetShipmentsResponse());

    $repository             = $pdk->get(ShipmentRepository::class);
    $inputShipments         = (new Collection($input))->mapInto(Shipment::class);
    $createdReturnShipments = $repository->createReturnShipments(new ShipmentCollection($inputShipments->all()));
    $array                  = $createdReturnShipments->toArray();

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
                'sender'              => DEFAULT_INPUT_SENDER,
            ],
        ],
    ],
]);

it('creates a valid request from a shipment collection', function ($input, $path, $query, $contentType) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostIdsResponse());
    $mock->append(new ExampleGetShipmentsResponse());

    $repository = $pdk->get(ShipmentRepository::class);
    $response   = $repository->createReturnShipments(new ShipmentCollection($input));
    $request    = $mock->getLastRequest();

    if (! $request) {
        throw new RuntimeException('Request is not set');
    }

    $uri = $request->getUri();

    expect($uri->getQuery())
        ->toBe($query)
        ->and($uri->getPath())
        ->toBe($path)
        ->and($response)
        ->toBeInstanceOf(ShipmentCollection::class);
})->with([
    'single shipment' => [
        'input'       => [
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
                'sender'              => DEFAULT_INPUT_SENDER,
            ],
        ],
        'path'        => 'API/shipments/1',
        'query'       => '',
        'contentType' => 'application/vnd.return_shipment+json;charset=utf-8',
    ],
]);
