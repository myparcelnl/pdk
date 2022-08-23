<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Api\Response\GetOrdersResponse;
use MyParcelNL\Pdk\Tests\Api\Response\PostOrdersResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

const DEFAULT_OUTPUT_RECIPIENT = [
    'recipient.cc'          => 'NL',
    'recipient.city'        => 'Hoofddorp',
    'recipient.person'      => 'Jaappie Krekel',
    'recipient.postal_code' => '2132JE',
    'recipient.street'      => 'Antareslaan 31',
];

const DEFAULT_INPUT_RECIPIENT = [
    'cc'         => 'NL',
    'city'       => 'Hoofddorp',
    'person'     => 'Jaappie Krekel',
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

it('creates a valid request from an order collection', function (array $input, array $output) {
    $pdk = PdkFactory::create(MockPdkConfig::create());
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new PostOrdersResponse());

    $inputShipments = (new Collection($input))->mapInto(Order::class);

    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository $repository */
    $repository = $pdk->get(OrderRepository::class);

    $savedOrders = $repository->saveOrder(
        new OrderCollection($inputShipments->all())
    );

    $request = $mock->getLastRequest();

    if (! $request) {
        throw new RuntimeException('Request not found.');
    }

    $body = json_decode(
        $request->getBody()
            ->getContents(),
        true
    );

    $orders = Arr::get($body, 'data.orders');

    expect($orders)
        ->toBeArray()
        ->and($savedOrders)
        ->toBeInstanceOf(OrderCollection::class)
        ->and(
            array_map(function (array $order) {
                return Arr::dot($order);
            }, $orders)
        )
        ->toEqual($output);
})->with([
    'order with one product' => [
        'input'  => [
            [
                'invoiceAddress' => null,
                'language'       => null,
                'orderDate'      => '2022-08-22 00:00:00',
                'orderLines'     => [
                    'uuid'          => '1234',
                    'quantity'      => 1,
                    'price'         => 250,
                    'vat'           => 10,
                    'priceAfterVat' => 260,
                    'product'       => [
                        'uuid'               => '12345',
                        'sku'                => '018234',
                        'ean'                => '018234',
                        'externalIdentifier' => '018234',
                        'name'               => 'Paarse stofzuiger',
                        'description'        => 'Een paars object waarmee stof opgezogen kan worden',
                        'width'              => null,
                        'length'             => null,
                        'height'             => null,
                        'weight'             => 3500,
                    ],
                ],
                'price'          => 260,
                'shipment'       => [
                    'apiKey'             => '123',
                    'carrier'            => [
                        'id' => CarrierOptions::CARRIER_POSTNL_ID,
                    ],
                    'customsDeclaration' => null,
                    'deliveryOptions'    => [
                        'carrier'         => CarrierOptions::CARRIER_POSTNL_NAME,
                        'date'            => '2022-08-22 00:00:00',
                        'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                        'packageType'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        'pickupLocation'  => null,
                        'shipmentOptions' => [
                            'ageCheck'         => true,
                            'insurance'        => 0,
                            'labelDescription' => null,
                            'largeFormat'      => false,
                            'onlyRecipient'    => false,
                            'return'           => false,
                            'sameDayDelivery'  => false,
                            'signature'        => true,
                        ],
                    ],
                    'dropOffPoint'       => null,
                    'physicalProperties' => [
                        'weight' => 3500,
                    ],
                    'recipient'          => DEFAULT_INPUT_RECIPIENT,
                    'sender'             => DEFAULT_INPUT_SENDER,
                ],
                'shopId'         => null,
                'status'         => null,
                'type'           => null,
                'updatedAt'      => null,
                'uuid'           => null,
                'vat'            => null,
            ],
        ],
        'output' => [
            array_merge(DEFAULT_OUTPUT_RECIPIENT, [
                'carrier'              => CarrierOptions::CARRIER_POSTNL_ID,
                'options.package_type' => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
            ]),
        ],
    ],
]);

it('creates shipment', function ($input, $path, $query, $contentType) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new GetOrdersResponse());

    $repository = $pdk->get(OrderRepository::class);

    $response = $repository->createConcepts(new OrderCollection($input));
    $request  = $mock->getLastRequest();

    if (! $request) {
        throw new RuntimeException('Request is not set');
    }

    $uri               = $request->getUri();
    $contentTypeHeader = Arr::first($request->getHeaders()['Content-Type']);

    expect($uri->getQuery())
        ->toBe($query)
        ->and($uri->getPath())
        ->toBe($path)
        ->and($contentTypeHeader)
        ->toBe($contentType)
        ->and($response)
        ->toBeInstanceOf(OrderCollection::class);
})->with([
    'single shipment' => [
        'input'       => [
            [
                'carrier'            => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
                'deliveryOptions'    => [
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
                'physicalProperties' => [
                    'height' => 100,
                    'width'  => 120,
                    'length' => 80,
                    'weight' => 2000,
                ],
                'recipient'          => DEFAULT_INPUT_RECIPIENT,
                'sender'             => DEFAULT_INPUT_SENDER,
            ],
        ],
        'path'        => 'API/shipments',
        'query'       => '',
        'contentType' => 'application/vnd.shipment+json;charset=utf-8;version=1.1',
    ],
]);
