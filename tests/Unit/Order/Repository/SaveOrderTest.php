<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetOrdersResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrdersResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

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

it('creates a valid order collection from api data', function (array $input, array $output) {
    $pdk = PdkFactory::create(MockPdkConfig::create());
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostOrdersResponse());

    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository $repository */
    $repository  = $pdk->get(OrderRepository::class);
    $savedOrders = $repository->saveOrder(new OrderCollection($input));

    expect($savedOrders)
        ->toBeInstanceOf(OrderCollection::class)
        ->and(Arr::dot($savedOrders->toArray()))
        ->toHaveKeysAndValues($output);
})->with([
    'order containing many attributes' => [
        'input'  => [
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
                        'id' => CarrierOptions::CARRIER_POSTNL_ID,
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
            '0.orderDate'                                                 => '2022-08-22 00:00:00',
            '0.orderLines.0.price'                                        => 250,
            '0.orderLines.0.priceAfterVat'                                => 260,
            '0.orderLines.0.product.description'                          => 'Een paars object waarmee stof opgezogen kan worden',
            '0.orderLines.0.product.ean'                                  => '018234',
            '0.orderLines.0.product.externalIdentifier'                   => '018234',
            '0.orderLines.0.product.height'                               => null,
            '0.orderLines.0.product.length'                               => null,
            '0.orderLines.0.product.name'                                 => 'Paarse stofzuiger',
            '0.orderLines.0.product.sku'                                  => '018234',
            '0.orderLines.0.product.uuid'                                 => '12345',
            '0.orderLines.0.product.weight'                               => 3500,
            '0.orderLines.0.product.width'                                => null,
            '0.orderLines.0.quantity'                                     => 1,
            '0.orderLines.0.uuid'                                         => '1234',
            '0.orderLines.0.vat'                                          => 10,
            '0.price'                                                     => 260,
            '0.priceAfterVat'                                             => null,
            '0.shipment.apiKey'                                           => '123',
            '0.shipment.carrier.id'                                       => 1,
            '0.shipment.carrier.name'                                     => 'postnl',
            '0.shipment.customsDeclaration.contents'                      => 1,
            '0.shipment.customsDeclaration.invoice'                       => '25',
            '0.shipment.customsDeclaration.items.0.amount'                => 1,
            '0.shipment.customsDeclaration.items.0.classification'        => '5256',
            '0.shipment.customsDeclaration.items.0.country'               => 'BE',
            '0.shipment.customsDeclaration.items.0.description'           => 'Vlaamse Patatekes',
            '0.shipment.customsDeclaration.items.0.itemValue.amount'      => 5000,
            '0.shipment.customsDeclaration.items.0.itemValue.currency'    => 'EUR',
            '0.shipment.customsDeclaration.items.0.weight'                => 200,
            '0.shipment.customsDeclaration.items.1.amount'                => 1,
            '0.shipment.customsDeclaration.items.1.classification'        => '9221',
            '0.shipment.customsDeclaration.items.1.country'               => 'FR',
            '0.shipment.customsDeclaration.items.1.description'           => 'Omelette du Fromage',
            '0.shipment.customsDeclaration.items.1.itemValue.amount'      => 10000,
            '0.shipment.customsDeclaration.items.1.itemValue.currency'    => 'EUR',
            '0.shipment.customsDeclaration.items.1.weight'                => 350,
            '0.shipment.customsDeclaration.weight'                        => 550,
            '0.shipment.delayed'                                          => false,
            '0.shipment.delivered'                                        => false,
            '0.shipment.deliveryOptions.carrier'                          => 'postnl',
            '0.shipment.deliveryOptions.date'                             => '2022-08-22 00:00:00',
            '0.shipment.deliveryOptions.deliveryType'                     => 'standard',
            '0.shipment.deliveryOptions.packageType'                      => 'package',
            '0.shipment.deliveryOptions.shipmentOptions.ageCheck'         => true,
            '0.shipment.deliveryOptions.shipmentOptions.insurance'        => 0,
            '0.shipment.deliveryOptions.shipmentOptions.labelDescription' => null,
            '0.shipment.deliveryOptions.shipmentOptions.largeFormat'      => false,
            '0.shipment.deliveryOptions.shipmentOptions.onlyRecipient'    => false,
            '0.shipment.deliveryOptions.shipmentOptions.return'           => false,
            '0.shipment.deliveryOptions.shipmentOptions.sameDayDelivery'  => false,
            '0.shipment.deliveryOptions.shipmentOptions.signature'        => true,
            '0.shipment.isReturn'                                         => false,
            '0.shipment.multiCollo'                                       => false,
            '0.shipment.physicalProperties.weight'                        => 3500,
            '0.shipment.recipient.cc'                                     => 'NL',
            '0.shipment.recipient.city'                                   => 'Hoofddorp',
            '0.shipment.recipient.person'                                 => 'Jaap Krekel',
            '0.shipment.recipient.postalCode'                             => '2132JE',
            '0.shipment.recipient.street'                                 => 'Antareslaan 31',
            '0.shipment.sender.cc'                                        => 'NL',
            '0.shipment.sender.city'                                      => 'Amsterdam',
            '0.shipment.sender.number'                                    => '2',
            '0.shipment.sender.person'                                    => 'Willem Wever',
            '0.shipment.sender.postalCode'                                => '4164ZF',
            '0.shipment.sender.street'                                    => 'Werf',
            '0.uuid'                                                      => '123',
        ],
    ],
    'order with pickup'                => [
        'input'  => [
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
                        'id' => CarrierOptions::CARRIER_POSTNL_ID,
                    ],
                    'deliveryOptions'    => [
                        'carrier'         => CarrierOptions::CARRIER_POSTNL_NAME,
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
        'output' => [
            '0.orderDate'                                                => '2022-08-22 00:00:00',
            '0.orderLines.0.price'                                       => 250,
            '0.orderLines.0.priceAfterVat'                               => 260,
            '0.orderLines.0.product.description'                         => 'Een paars object waarmee stof opgezogen kan worden',
            '0.orderLines.0.product.ean'                                 => '018234',
            '0.orderLines.0.product.externalIdentifier'                  => '018234',
            '0.orderLines.0.product.height'                              => null,
            '0.orderLines.0.product.length'                              => null,
            '0.orderLines.0.product.name'                                => 'Paarse stofzuiger',
            '0.orderLines.0.product.sku'                                 => '018234',
            '0.orderLines.0.product.uuid'                                => '12345',
            '0.orderLines.0.product.weight'                              => 3500,
            '0.orderLines.0.product.width'                               => null,
            '0.orderLines.0.quantity'                                    => 1,
            '0.orderLines.0.uuid'                                        => '1234',
            '0.orderLines.0.vat'                                         => 10,
            '0.price'                                                    => 260,
            '0.shipment.apiKey'                                          => '123',
            '0.shipment.carrier.id'                                      => 1,
            '0.shipment.carrier.name'                                    => 'postnl',
            '0.shipment.carrier.primary'                                 => true,
            '0.shipment.carrier.type'                                    => 'main',
            '0.shipment.delayed'                                         => false,
            '0.shipment.delivered'                                       => false,
            '0.shipment.deliveryOptions.carrier'                         => 'postnl',
            '0.shipment.deliveryOptions.date'                            => '2022-08-22 00:00:00',
            '0.shipment.deliveryOptions.deliveryType'                    => 'pickup',
            '0.shipment.deliveryOptions.packageType'                     => 'package',
            '0.shipment.deliveryOptions.pickupLocation.locationCode'     => '122',
            '0.shipment.deliveryOptions.shipmentOptions.ageCheck'        => true,
            '0.shipment.deliveryOptions.shipmentOptions.insurance'       => 0,
            '0.shipment.deliveryOptions.shipmentOptions.largeFormat'     => false,
            '0.shipment.deliveryOptions.shipmentOptions.onlyRecipient'   => false,
            '0.shipment.deliveryOptions.shipmentOptions.return'          => false,
            '0.shipment.deliveryOptions.shipmentOptions.sameDayDelivery' => false,
            '0.shipment.deliveryOptions.shipmentOptions.signature'       => true,
            '0.shipment.isReturn'                                        => false,
            '0.shipment.multiCollo'                                      => false,
            '0.shipment.physicalProperties.weight'                       => 3500,
            '0.shipment.recipient.cc'                                    => 'NL',
            '0.shipment.recipient.city'                                  => 'Hoofddorp',
            '0.shipment.recipient.person'                                => 'Jaap Krekel',
            '0.shipment.recipient.postalCode'                            => '2132JE',
            '0.shipment.recipient.street'                                => 'Antareslaan 31',
            '0.shipment.sender.cc'                                       => 'NL',
            '0.shipment.sender.city'                                     => 'Amsterdam',
            '0.shipment.sender.number'                                   => '2',
            '0.shipment.sender.person'                                   => 'Willem Wever',
            '0.shipment.sender.postalCode'                               => '4164ZF',
            '0.shipment.sender.street'                                   => 'Werf',
            '0.uuid'                                                     => '123',
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
    $response = $repository->saveOrder($orderCollection);
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
                    'id' => CarrierOptions::CARRIER_POSTNL_ID,
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
                    'street'     => 'Antareslaan 31',
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
