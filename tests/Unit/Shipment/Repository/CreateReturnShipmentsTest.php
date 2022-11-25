<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

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

it('creates return shipment', function (array $input, array $output) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostShipmentsResponse());

    $repository             = $pdk->get(ShipmentRepository::class);
    $inputShipments         = (new Collection($input))->mapInto(Shipment::class);
    $createdReturnShipments = $repository->createReturnShipments(new ShipmentCollection($inputShipments->all()));

    $request = $mock->getLastRequest();

    if (! $request) {
        throw new RuntimeException('Request is not set');
    }

    $body = json_decode(
        $request->getBody()
            ->getContents(),
        true
    );

    $shipments = Arr::get($body, 'data.return_shipments');

    expect($shipments)
        ->toBeArray()
        ->and($createdReturnShipments)
        ->toBeInstanceOf(ShipmentCollection::class)
        ->and(Arr::dot($shipments))
        ->toEqual($output);
})->with([
    'simple domestic shipment' => [
        'input'  => [
            [
                'id'                  => 65435213,
                'carrier'             => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
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
                'referenceIdentifier' => 'Order-1',
                'sender'              => DEFAULT_INPUT_SENDER,
            ],
        ],
        'output' => [
            '0.parent'                     => 65435213,
            '0.reference_identifier'       => 'Order-1',
            '0.carrier'                    => 1,
            '0.email'                      => 'test@myparcel.nl',
            '0.name'                       => 'Jaap Krekel',
            '0.options.package_type'       => 1,
            '0.options.age_check'          => 1,
            '0.options.label_description'  => 'order 204829',
            '0.options.only_recipient'     => 1,
            '0.options.insurance.amount'   => 50000,
            '0.options.insurance.currency' => 'EUR',
        ],
    ],
    'multiple shipments'       => [
        'input'  => [
            [
                'id'                  => 4321,
                'carrier'             => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
                'deliveryOptions'     => [
                    'date'            => '2022-07-20 16:00:00',
                    'shipmentOptions' => [
                        'ageCheck'         => true,
                        'insurance'        => 0,
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
                'referenceIdentifier' => 'Bestelling-12',
                'sender'              => DEFAULT_INPUT_SENDER,
            ],
            [
                'id'                  => 24,
                'carrier'             => ['id' => CarrierOptions::CARRIER_INSTABOX_ID],
                'deliveryOptions'     => [
                    'date'            => '2022-07-20 16:00:00',
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
                'referenceIdentifier' => 'Hondenbrokken-43',
                'sender'              => DEFAULT_INPUT_SENDER,
            ],
        ],
        'output' => [
            '0.parent'                     => 4321,
            '0.reference_identifier'       => 'Bestelling-12',
            '0.carrier'                    => 1,
            '0.email'                      => 'test@myparcel.nl',
            '0.name'                       => 'Jaap Krekel',
            '0.options.package_type'       => 1,
            '0.options.age_check'          => 1,
            '0.options.label_description'  => 'order 204829',
            '0.options.only_recipient'     => 1,
            '1.parent'                     => 24,
            '1.reference_identifier'       => 'Hondenbrokken-43',
            '1.carrier'                    => 5,
            '1.email'                      => 'test@myparcel.nl',
            '1.name'                       => 'Jaap Krekel',
            '1.options.package_type'       => 1,
            '1.options.age_check'          => 1,
            '1.options.label_description'  => 'order 204829',
            '1.options.only_recipient'     => 1,
            '1.options.insurance.amount'   => 50000,
            '1.options.insurance.currency' => 'EUR',
        ],
    ],
]);

it('creates a valid request from a shipment collection', function ($input, $path, $query, $contentType) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostShipmentsResponse());
    $mock->append(new ExampleGetShipmentsResponse());

    $repository = $pdk->get(ShipmentRepository::class);
    $response   = $repository->createReturnShipments(new ShipmentCollection($input));
    $request    = $mock->getLastRequest();

    if (! $request) {
        throw new RuntimeException('Request is not set');
    }

    $uri               = $request->getUri();
    $contentTypeHeader = Arr::first($request->getHeaders()['Content-Type']);

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
                'carrier'             => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
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
