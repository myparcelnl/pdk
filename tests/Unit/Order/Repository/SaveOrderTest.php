<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
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

    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository $repository */
    $repository  = $pdk->get(OrderRepository::class);
    $savedOrders = $repository->saveOrder(new OrderCollection($input));

    $vadfw = $savedOrders->map(function (Order $order) {
        return Arr::dot(
            Arr::except(
                $order->toArray(),
                ['shipment.carrier.capabilities', 'shipment.carrier.returnCapabilities']
            )
        );
    })
        ->toArray();

    expect($savedOrders)
        ->toBeInstanceOf(OrderCollection::class)
        ->and(
            $savedOrders->map(function (Order $order) {
                return Arr::dot(
                    Arr::except(
                        $order->toArray(),
                        ['shipment.carrier.capabilities', 'shipment.carrier.returnCapabilities']
                    )
                );
            })
                ->toArray()[0]
        )
        ->toEqual($output);
})->with([
    'order containing many attributes' => [
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
                    'customsDeclaration' => [
                        'contents' => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
                        'invoice'  => '25',
                        'items'    => [
                            [
                                'amount'         => 1,
                                'classification' => 5256,
                                'country'        => CountryCodes::CC_BE,
                                'description'    => 'Vlaamse Patatekes',
                                'itemValue'      => ['amount' => 5000, 'currency' => 'EUR'],
                                'weight'         => 200,
                            ],
                            [
                                'amount'         => 1,
                                'classification' => 9221,
                                'country'        => CountryCodes::CC_FR,
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
            'accountId'                                                 => null,
            'createdAt'                                                 => null,
            'externalIdentifier'                                        => null,
            'fulfilmentPartnerIdentifier'                               => null,
            'invoiceAddress'                                            => null,
            'language'                                                  => null,
            'orderDate'                                                 => '2022-08-22 00:00:00',
            'orderLines.uuid'                                           => '1234',
            'orderLines.quantity'                                       => 1,
            'orderLines.price'                                          => 250,
            'orderLines.vat'                                            => 10,
            'orderLines.priceAfterVat'                                  => 260,
            'orderLines.product.uuid'                                   => '12345',
            'orderLines.product.sku'                                    => '018234',
            'orderLines.product.ean'                                    => '018234',
            'orderLines.product.externalIdentifier'                     => '018234',
            'orderLines.product.name'                                   => 'Paarse stofzuiger',
            'orderLines.product.description'                            => 'Een paars object waarmee stof opgezogen kan worden',
            'orderLines.product.width'                                  => null,
            'orderLines.product.length'                                 => null,
            'orderLines.product.height'                                 => null,
            'orderLines.product.weight'                                 => 3500,
            'price'                                                     => 260,
            'priceAfterVat'                                             => null,
            'shipment.id'                                               => null,
            'shipment.referenceIdentifier'                              => null,
            'shipment.externalIdentifier'                               => null,
            'shipment.apiKey'                                           => '123',
            'shipment.barcode'                                          => null,
            'shipment.carrier.id'                                       => 1,
            'shipment.carrier.name'                                     => 'postnl',
            'shipment.carrier.human'                                    => null,
            'shipment.carrier.subscriptionId'                           => null,
            'shipment.carrier.primary'                                  => true,
            'shipment.carrier.isDefault'                                => null,
            'shipment.carrier.optional'                                 => null,
            'shipment.carrier.label'                                    => null,
            'shipment.carrier.type'                                     => 'main',
            'shipment.collectionContact'                                => null,
            'shipment.created'                                          => null,
            'shipment.createdBy'                                        => null,
            'shipment.customsDeclaration.contents'                      => 1,
            'shipment.customsDeclaration.invoice'                       => '25',
            'shipment.customsDeclaration.items.0.amount'                => 1,
            'shipment.customsDeclaration.items.0.classification'        => '5256',
            'shipment.customsDeclaration.items.0.country'               => 'BE',
            'shipment.customsDeclaration.items.0.description'           => 'Vlaamse Patatekes',
            'shipment.customsDeclaration.items.0.itemValue.amount'      => 5000,
            'shipment.customsDeclaration.items.0.itemValue.currency'    => 'EUR',
            'shipment.customsDeclaration.items.0.weight'                => 200,
            'shipment.customsDeclaration.items.1.amount'                => 1,
            'shipment.customsDeclaration.items.1.classification'        => '9221',
            'shipment.customsDeclaration.items.1.country'               => 'FR',
            'shipment.customsDeclaration.items.1.description'           => 'Omelette du Fromage',
            'shipment.customsDeclaration.items.1.itemValue.amount'      => 10000,
            'shipment.customsDeclaration.items.1.itemValue.currency'    => 'EUR',
            'shipment.customsDeclaration.items.1.weight'                => 350,
            'shipment.customsDeclaration.weight'                        => 550,
            'shipment.delayed'                                          => false,
            'shipment.delivered'                                        => false,
            'shipment.deliveryOptions.carrier'                          => 'postnl',
            'shipment.deliveryOptions.date'                             => '2022-08-22 00:00:00',
            'shipment.deliveryOptions.deliveryType'                     => 'standard',
            'shipment.deliveryOptions.packageType'                      => 'package',
            'shipment.deliveryOptions.pickupLocation'                   => null,
            'shipment.deliveryOptions.shipmentOptions.ageCheck'         => true,
            'shipment.deliveryOptions.shipmentOptions.insurance'        => 0,
            'shipment.deliveryOptions.shipmentOptions.labelDescription' => null,
            'shipment.deliveryOptions.shipmentOptions.largeFormat'      => false,
            'shipment.deliveryOptions.shipmentOptions.onlyRecipient'    => false,
            'shipment.deliveryOptions.shipmentOptions.return'           => false,
            'shipment.deliveryOptions.shipmentOptions.sameDayDelivery'  => false,
            'shipment.deliveryOptions.shipmentOptions.signature'        => true,
            'shipment.dropOffPoint'                                     => null,
            'shipment.isReturn'                                         => false,
            'shipment.linkConsumerPortal'                               => null,
            'shipment.modified'                                         => null,
            'shipment.modifiedBy'                                       => null,
            'shipment.multiCollo'                                       => false,
            'shipment.multiColloMainShipmentId'                         => null,
            'shipment.partnerTrackTraces'                               => null,
            'shipment.physicalProperties.height'                        => null,
            'shipment.physicalProperties.length'                        => null,
            'shipment.physicalProperties.weight'                        => 3500,
            'shipment.physicalProperties.width'                         => null,
            'shipment.recipient.boxNumber'                              => null,
            'shipment.recipient.cc'                                     => 'NL',
            'shipment.recipient.city'                                   => 'Hoofddorp',
            'shipment.recipient.fullStreet'                             => null,
            'shipment.recipient.number'                                 => null,
            'shipment.recipient.numberSuffix'                           => null,
            'shipment.recipient.postalCode'                             => '2132JE',
            'shipment.recipient.region'                                 => null,
            'shipment.recipient.state'                                  => null,
            'shipment.recipient.street'                                 => 'Antareslaan 31',
            'shipment.recipient.streetAdditionalInfo'                   => null,
            'shipment.recipient.email'                                  => null,
            'shipment.recipient.phone'                                  => null,
            'shipment.recipient.person'                                 => 'Jaappie Krekel',
            'shipment.recipient.company'                                => null,
            'shipment.sender.boxNumber'                                 => null,
            'shipment.sender.cc'                                        => 'NL',
            'shipment.sender.city'                                      => 'Amsterdam',
            'shipment.sender.fullStreet'                                => null,
            'shipment.sender.number'                                    => '2',
            'shipment.sender.numberSuffix'                              => null,
            'shipment.sender.postalCode'                                => '4164ZF',
            'shipment.sender.region'                                    => null,
            'shipment.sender.state'                                     => null,
            'shipment.sender.street'                                    => 'Werf',
            'shipment.sender.streetAdditionalInfo'                      => null,
            'shipment.sender.email'                                     => null,
            'shipment.sender.phone'                                     => null,
            'shipment.sender.person'                                    => 'Willem Wever',
            'shipment.sender.company'                                   => null,
            'shipment.shopId'                                           => null,
            'shipment.status'                                           => null,
            'shipment.updated'                                          => null,
            'shopId'                                                    => null,
            'status'                                                    => null,
            'type'                                                      => null,
            'updatedAt'                                                 => null,
            'uuid'                                                      => '123',
            'vat'                                                       => null,
        ],
    ],
    'order with pickup'                => [
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
            'accountId'                                                    => null,
            'createdAt'                                                    => null,
            'externalIdentifier'                                           => null,
            'fulfilmentPartnerIdentifier'                                  => null,
            'invoiceAddress'                                               => null,
            'language'                                                     => null,
            'orderDate'                                                    => '2022-08-22 00:00:00',
            'orderLines.uuid'                                              => '1234',
            'orderLines.quantity'                                          => 1,
            'orderLines.price'                                             => 250,
            'orderLines.vat'                                               => 10,
            'orderLines.priceAfterVat'                                     => 260,
            'orderLines.product.uuid'                                      => '12345',
            'orderLines.product.sku'                                       => '018234',
            'orderLines.product.ean'                                       => '018234',
            'orderLines.product.externalIdentifier'                        => '018234',
            'orderLines.product.name'                                      => 'Paarse stofzuiger',
            'orderLines.product.description'                               => 'Een paars object waarmee stof opgezogen kan worden',
            'orderLines.product.width'                                     => null,
            'orderLines.product.length'                                    => null,
            'orderLines.product.height'                                    => null,
            'orderLines.product.weight'                                    => 3500,
            'price'                                                        => 260,
            'priceAfterVat'                                                => null,
            'shipment.id'                                                  => null,
            'shipment.referenceIdentifier'                                 => null,
            'shipment.externalIdentifier'                                  => null,
            'shipment.apiKey'                                              => '123',
            'shipment.barcode'                                             => null,
            'shipment.carrier.id'                                          => 1,
            'shipment.carrier.name'                                        => 'postnl',
            'shipment.carrier.human'                                       => null,
            'shipment.carrier.subscriptionId'                              => null,
            'shipment.carrier.primary'                                     => true,
            'shipment.carrier.isDefault'                                   => null,
            'shipment.carrier.optional'                                    => null,
            'shipment.carrier.label'                                       => null,
            'shipment.carrier.type'                                        => 'main',
            'shipment.collectionContact'                                   => null,
            'shipment.created'                                             => null,
            'shipment.createdBy'                                           => null,
            'shipment.customsDeclaration'                                  => null,
            'shipment.delayed'                                             => false,
            'shipment.delivered'                                           => false,
            'shipment.deliveryOptions.carrier'                             => 'postnl',
            'shipment.deliveryOptions.date'                                => '2022-08-22 00:00:00',
            'shipment.deliveryOptions.deliveryType'                        => 'pickup',
            'shipment.deliveryOptions.packageType'                         => 'package',
            'shipment.deliveryOptions.pickupLocation.boxNumber'            => null,
            'shipment.deliveryOptions.pickupLocation.cc'                   => null,
            'shipment.deliveryOptions.pickupLocation.city'                 => null,
            'shipment.deliveryOptions.pickupLocation.fullStreet'           => null,
            'shipment.deliveryOptions.pickupLocation.number'               => null,
            'shipment.deliveryOptions.pickupLocation.numberSuffix'         => null,
            'shipment.deliveryOptions.pickupLocation.postalCode'           => null,
            'shipment.deliveryOptions.pickupLocation.region'               => null,
            'shipment.deliveryOptions.pickupLocation.state'                => null,
            'shipment.deliveryOptions.pickupLocation.street'               => null,
            'shipment.deliveryOptions.pickupLocation.streetAdditionalInfo' => null,
            'shipment.deliveryOptions.pickupLocation.locationCode'         => '122',
            'shipment.deliveryOptions.pickupLocation.locationName'         => null,
            'shipment.deliveryOptions.pickupLocation.retailNetworkId'      => null,
            'shipment.deliveryOptions.shipmentOptions.ageCheck'            => true,
            'shipment.deliveryOptions.shipmentOptions.insurance'           => 0,
            'shipment.deliveryOptions.shipmentOptions.labelDescription'    => null,
            'shipment.deliveryOptions.shipmentOptions.largeFormat'         => false,
            'shipment.deliveryOptions.shipmentOptions.onlyRecipient'       => false,
            'shipment.deliveryOptions.shipmentOptions.return'              => false,
            'shipment.deliveryOptions.shipmentOptions.sameDayDelivery'     => false,
            'shipment.deliveryOptions.shipmentOptions.signature'           => true,
            'shipment.dropOffPoint'                                        => null,
            'shipment.isReturn'                                            => false,
            'shipment.linkConsumerPortal'                                  => null,
            'shipment.modified'                                            => null,
            'shipment.modifiedBy'                                          => null,
            'shipment.multiCollo'                                          => false,
            'shipment.multiColloMainShipmentId'                            => null,
            'shipment.partnerTrackTraces'                                  => null,
            'shipment.physicalProperties.height'                           => null,
            'shipment.physicalProperties.length'                           => null,
            'shipment.physicalProperties.weight'                           => 3500,
            'shipment.physicalProperties.width'                            => null,
            'shipment.recipient.boxNumber'                                 => null,
            'shipment.recipient.cc'                                        => 'NL',
            'shipment.recipient.city'                                      => 'Hoofddorp',
            'shipment.recipient.fullStreet'                                => null,
            'shipment.recipient.number'                                    => null,
            'shipment.recipient.numberSuffix'                              => null,
            'shipment.recipient.postalCode'                                => '2132JE',
            'shipment.recipient.region'                                    => null,
            'shipment.recipient.state'                                     => null,
            'shipment.recipient.street'                                    => 'Antareslaan 31',
            'shipment.recipient.streetAdditionalInfo'                      => null,
            'shipment.recipient.email'                                     => null,
            'shipment.recipient.phone'                                     => null,
            'shipment.recipient.person'                                    => 'Jaappie Krekel',
            'shipment.recipient.company'                                   => null,
            'shipment.sender.boxNumber'                                    => null,
            'shipment.sender.cc'                                           => 'NL',
            'shipment.sender.city'                                         => 'Amsterdam',
            'shipment.sender.fullStreet'                                   => null,
            'shipment.sender.number'                                       => '2',
            'shipment.sender.numberSuffix'                                 => null,
            'shipment.sender.postalCode'                                   => '4164ZF',
            'shipment.sender.region'                                       => null,
            'shipment.sender.state'                                        => null,
            'shipment.sender.street'                                       => 'Werf',
            'shipment.sender.streetAdditionalInfo'                         => null,
            'shipment.sender.email'                                        => null,
            'shipment.sender.phone'                                        => null,
            'shipment.sender.person'                                       => 'Willem Wever',
            'shipment.sender.company'                                      => null,
            'shipment.shopId'                                              => null,
            'shipment.status'                                              => null,
            'shipment.updated'                                             => null,
            'shopId'                                                       => null,
            'status'                                                       => null,
            'type'                                                         => null,
            'updatedAt'                                                    => null,
            'uuid'                                                         => '123',
            'vat'                                                          => null,
        ],
    ],
]);

it('creates order', function ($input, $path, $query) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new GetOrdersResponse());

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
                    'person'     => 'Jaappie Krekel',
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
