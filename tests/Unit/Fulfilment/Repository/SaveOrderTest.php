<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Fulfilment\Repository;

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetOrdersResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrdersResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use RuntimeException;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

const DEFAULT_INPUT_RECIPIENT = [
    'cc'         => 'NL',
    'city'       => 'Hoofddorp',
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

it('creates a valid order collection from api data', function (array $input) {
    $pdk = PdkFactory::create(MockPdkConfig::create());
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostOrdersResponse());

    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository $repository */
    $repository  = $pdk->get(OrderRepository::class);
    $savedOrders = $repository->postOrders(new OrderCollection($input));

    expect($savedOrders)
        ->toBeInstanceOf(OrderCollection::class);

    assertMatchesJsonSnapshot(json_encode($savedOrders->toArray()));
})->with([
    'order containing many attributes' => [
        'input' => [
            [
                'invoiceAddress' => null,
                'language'       => null,
                'orderDate'      => '2022-08-22 00:00:00',
                'orderLines'     => [
                    [
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
                ],
                'price'          => 260,
                'shipment'       => [
                    'apiKey'             => '123',
                    'carrier'            => [
                        'id' => Carrier::CARRIER_POSTNL_ID,
                    ],
                    'customsDeclaration' => [
                        'contents' => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
                        'invoice'  => '25',
                        'items'    => [
                            [
                                'amount'         => 1,
                                'classification' => 5256,
                                'country'        => CountryService::CC_BE,
                                'description'    => 'Vlaamse Patatekes',
                                'itemValue'      => ['amount' => 5000, 'currency' => 'EUR'],
                                'weight'         => 200,
                            ],
                            [
                                'amount'         => 1,
                                'classification' => 9221,
                                'country'        => CountryService::CC_FR,
                                'description'    => 'Omelette du Fromage',
                                'itemValue'      => ['amount' => 10000, 'currency' => 'EUR'],
                                'weight'         => 350,
                            ],
                        ],
                    ],
                    'deliveryOptions'    => [
                        'carrier'         => Carrier::CARRIER_POSTNL_NAME,
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
    ],
    'order with pickup'                => [
        'input' => [
            [
                'invoiceAddress' => null,
                'language'       => null,
                'orderDate'      => '2022-08-22 00:00:00',
                'orderLines'     => [
                    [
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
                ],
                'price'          => 260,
                'shipment'       => [
                    'apiKey'             => '123',
                    'carrier'            => [
                        'id' => Carrier::CARRIER_POSTNL_ID,
                    ],
                    'deliveryOptions'    => [
                        'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                        'date'            => '2022-08-22 00:00:00',
                        'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                        'packageType'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        'pickupLocation'  => [
                            'locationCode' => 0172,
                        ],
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
    ],
]);

it('creates order', function ($input, $path, $query) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExampleGetOrdersResponse());

    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository $repository */
    $repository      = $pdk->get(OrderRepository::class);
    $order           = new Order($input);
    $orderCollection = (new OrderCollection())->push($order);

    /** @var OrderRepository $response */
    $response = $repository->postOrders($orderCollection);
    $request  = $mock->getLastRequest();

    if (! $request) {
        throw new RuntimeException('Request is not set');
    }

    $uri = $request->getUri();

    expect($uri->getQuery())
        ->toBe($query)
        ->and($uri->getPath())
        ->toBe($path)
        ->and($response)
        ->toBeInstanceOf(OrderCollection::class);
})->with([
    'empty query with single shipment response' => [
        'input' => [
            'invoiceAddress' => [
                'cc'   => 'NL',
                'city' => 'Boskoop',
            ],
            'language'       => null,
            'orderDate'      => '2022-08-22 00:00:00',
            'orderLines'     => [
                [
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

            ],
            'price'          => 260,
            'shipment'       => [
                'carrier'            => [
                    'id' => Carrier::CARRIER_POSTNL_ID,
                ],
                'customsDeclaration' => null,
                'deliveryOptions'    => [
                    'date'            => '2022-08-22 00:00:00',
                    'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                    'packageType'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    'shipmentOptions' => [
                        'ageCheck'         => 1,
                        'insurance'        => [
                            'amount'   => 0,
                            'currency' => 'EUR',
                        ],
                        'labelDescription' => null,
                        'largeFormat'      => 0,
                        'onlyRecipient'    => 0,
                        'return'           => 0,
                        'sameDayDelivery'  => 0,
                        'signature'        => 1,
                    ],
                ],
                'dropOffPoint'       => null,
                'physicalProperties' => [
                    'weight' => 3500,
                ],
                'recipient'          => [
                    'cc'         => 'NL',
                    'city'       => 'Hoofddorp',
                    'person'     => 'Jaap Krekel',
                    'postalCode' => '2132JE',
                    'fullStreet' => 'Antareslaan 31',
                ],
                'sender'             => [
                    'cc'         => 'NL',
                    'city'       => 'Amsterdam',
                    'number'     => '2',
                    'person'     => 'Willem Wever',
                    'postalCode' => '4164ZF',
                    'street'     => 'Werf',
                ],
            ],
            'shopId'         => null,
            'status'         => null,
            'type'           => null,
            'updatedAt'      => null,
            'uuid'           => null,
            'vat'            => null,
        ],
        'path'  => 'API/fulfilment/orders',
        'query' => '',
    ],
]);
