<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\PdkActions;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsLinkV2Response;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Response;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance([
        AbstractPdkOrderRepository::class => autowire(MockPdkOrderRepository::class),
    ]),
    new UsesApiMock()
);

it('exports order', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $orderRepository */
    $orderRepository = Pdk::get(AbstractPdkOrderRepository::class);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);

    $api->getMock()
        ->append(new ExamplePostShipmentsResponse());

    $pdkOrders = [
        new PdkOrder(
            [
                'externalIdentifier' => '245',
                'deliveryOptions'    => [
                    'carrier'     => CarrierOptions::CARRIER_POSTNL_NAME,
                    'packageType' => 'package',
                    'labelAmount' => 2,
                ],
                'recipient'          => [
                    'cc'         => CountryCodes::CC_NL,
                    'street'     => 'Pietjestraat',
                    'number'     => '35',
                    'postalCode' => '2771BW',
                    'city'       => 'Bikinibroek',
                ],
            ]
        ),
        new PdkOrder(
            [
                'externalIdentifier' => '247',
                'recipient'          => [
                    'cc'          => 'NL',
                    'city'        => 'Hoofddorp',
                    'person'      => 'Felicia Parcel',
                    'postal_code' => '2132 JE',
                    'full_street' => 'Antareslaan 31',
                ],
                'deliveryOptions'    => [
                    'carrier'      => CarrierOptions::CARRIER_POSTNL_NAME,
                    'deliveryType' => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                ],
            ]
        ),
    ];

    $orderRepository->add(...$pdkOrders);

    $response = Pdk::execute(PdkActions::EXPORT_ORDER, [
        'orderIds' => ['245', '247'],
    ]);

    if (! $response) {
        throw new RuntimeException('Response is empty');
    }

    $content = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and(Arr::dot($content))
        ->toHaveKeysAndValues([
            'data.orders.0.externalIdentifier'                       => '245',
            'data.orders.0.deliveryOptions.carrier'                  => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.0.deliveryOptions.labelAmount'              => 2,
            'data.orders.0.deliveryOptions.packageType'              => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.0.shipments.0.deliveryOptions.carrier'      => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.0.shipments.0.deliveryOptions.deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            'data.orders.0.shipments.0.deliveryOptions.packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.0.shipments.0.id'                           => 123,
            'data.orders.0.shipments.0.orderId'                      => '245',

            'data.orders.1.externalIdentifier'                       => '247',
            'data.orders.1.deliveryOptions.carrier'                  => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.1.deliveryOptions.deliveryType'             => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
            'data.orders.1.deliveryOptions.labelAmount'              => 1,
            'data.orders.1.deliveryOptions.packageType'              => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.1.shipments.0.deliveryOptions.carrier'      => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.1.shipments.0.deliveryOptions.deliveryType' => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
            'data.orders.1.shipments.0.deliveryOptions.labelAmount'  => 1,
            'data.orders.1.shipments.0.deliveryOptions.packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.1.shipments.0.id'                           => 456,
            'data.orders.1.shipments.0.orderId'                      => '247',
        ])
        ->and($content['data']['orders'])
        ->toHaveLength(2)
        ->and($content['data']['orders'][0]['shipments'])
        ->toHaveLength(1)
        ->and($content['data']['orders'][1]['shipments'])
        ->toHaveLength(1)
        ->and($response->getStatusCode())
        ->toBe(200);
});

it('prints order', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $orderRepository */
    $orderRepository = Pdk::get(AbstractPdkOrderRepository::class);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);

    $api->getMock()
        ->append(
            new ExampleGetShipmentLabelsLinkV2Response()
        );

    $orderRepository->add(
        new PdkOrder(
            [
                'externalIdentifier' => '701',
                'shipments'          => [
                    [
                        'id'                  => 100001,
                        'referenceIdentifier' => '1',
                    ],
                    [
                        'id'                  => 100002,
                        'referenceIdentifier' => '2',
                        'deliveryOptions'     => [
                            'carrier'         => CarrierOptions::CARRIER_POSTNL_NAME,
                            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                            'shipmentOptions' => [
                                'signature' => true,
                            ],
                        ],
                    ],
                ],
            ]
        )
    );

    $response = Pdk::execute(PdkActions::PRINT_ORDER, [
        'orderIds' => ['701', '702'],
        'download' => true,
    ]);

    if (! $response) {
        throw new RuntimeException('Response is empty');
    }

    $content = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and(Arr::dot($content))
        ->toHaveKeysAndValues([
            'data.link' => null,
            'data.pdf'  => '{"data":{"pdf":{"url":"\/pdfs\/label_hash"}}}',
        ])
        ->and($response->getStatusCode())
        ->toBe(200);
});

it('exports and prints order', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $orderRepository */
    $orderRepository = Pdk::get(AbstractPdkOrderRepository::class);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);

    $api->getMock()
        ->append(
            new ExamplePostIdsResponse([
                ['id' => 30321, 'reference_identifier' => '263'],
                ['id' => 30322, 'reference_identifier' => '264'],
            ]),
            new ExampleGetShipmentLabelsLinkV2Response()
        );

    $orderRepository->add(
        new PdkOrder(
            [
                'externalIdentifier' => '263',
                'deliveryOptions'    => [
                    'carrier' => CarrierOptions::CARRIER_POSTNL_NAME,
                ],
            ]
        ),
        new PdkOrder(
            [
                'externalIdentifier' => '264',
                'deliveryOptions'    => [
                    'carrier'         => CarrierOptions::CARRIER_POSTNL_NAME,
                    'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                    'shipmentOptions' => [
                        'signature' => true,
                    ],
                ],
            ]
        )
    );

    $response = Pdk::execute(PdkActions::EXPORT_AND_PRINT_ORDER, [
        'orderIds' => ['263', '264'],
    ]);

    if (! $response) {
        throw new RuntimeException('Response is empty');
    }

    $content = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and(Arr::dot($content))
        ->toHaveKeysAndValues([
            'data.orders.0.externalIdentifier'                       => '263',
            'data.orders.0.deliveryOptions.carrier'                  => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.0.deliveryOptions.labelAmount'              => 1,
            'data.orders.0.deliveryOptions.packageType'              => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.0.shipments.0.deliveryOptions.carrier'      => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.0.shipments.0.deliveryOptions.deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            'data.orders.0.shipments.0.deliveryOptions.packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.0.shipments.0.id'                           => 30321,
            'data.orders.0.shipments.0.orderId'                      => '263',

            'data.orders.1.externalIdentifier'                                    => '264',
            'data.orders.1.deliveryOptions.carrier'                               => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.1.deliveryOptions.deliveryType'                          => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
            'data.orders.1.deliveryOptions.labelAmount'                           => 1,
            'data.orders.1.deliveryOptions.packageType'                           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.1.deliveryOptions.shipmentOptions.signature'             => true,
            'data.orders.1.shipments.0.deliveryOptions.carrier'                   => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.1.shipments.0.deliveryOptions.deliveryType'              => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
            'data.orders.1.shipments.0.deliveryOptions.labelAmount'               => 1,
            'data.orders.1.shipments.0.deliveryOptions.packageType'               => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.1.shipments.0.deliveryOptions.shipmentOptions.signature' => true,
            'data.orders.1.shipments.0.id'                                        => 30322,
            'data.orders.1.shipments.0.orderId'                                   => '264',
        ])
        ->and($content['data']['orders'])
        ->toHaveLength(2)
        ->and($content['data']['orders'][0]['shipments'])
        ->toHaveLength(1)
        ->and($content['data']['orders'][1]['shipments'])
        ->toHaveLength(1)
        ->and($response->getStatusCode())
        ->toBe(200);
});

it('exports return', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $orderRepository */
    $orderRepository = Pdk::get(AbstractPdkOrderRepository::class);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);

    $mock = $api->getMock();
    $mock->append(new ExamplePostIdsResponse([['id' => 30011], ['id' => 30012]]));
    $mock->append(new ExampleGetShipmentsResponse());

    $orderRepository->add(
        new PdkOrder(
            [
                'externalIdentifier' => '701',
                'shipments'          => [
                    [
                        'id'                  => 100001,
                        'referenceIdentifier' => '1',
                    ],
                    [
                        'id'                  => 100002,
                        'referenceIdentifier' => '2',
                        'deliveryOptions'     => [
                            'carrier'         => CarrierOptions::CARRIER_POSTNL_NAME,
                            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                            'shipmentOptions' => [
                                'signature' => true,
                            ],
                        ],
                    ],
                ],
            ]
        ),
        new PdkOrder(
            [
                'externalIdentifier' => '247',
                'deliveryOptions'    => [
                    'carrier'      => CarrierOptions::CARRIER_POSTNL_NAME,
                    'deliveryType' => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                ],
            ]
        )
    );

    $response = Pdk::execute(PdkActions::EXPORT_RETURN, [
        'orderIds' => ['701', '247'],
    ]);

    if (! $response) {
        throw new RuntimeException('Response is empty');
    }

    $content = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and(Arr::dot($content))
        ->toHaveKeysAndValues([
            /**
             * 245
             */
            'data.orders.0.externalIdentifier'           => '701',
            'data.orders.0.deliveryOptions.carrier'      => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.0.deliveryOptions.labelAmount'  => 1,
            'data.orders.0.deliveryOptions.packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.0.label'                        => null,
            'data.orders.1.externalIdentifier'           => '247',
            'data.orders.1.deliveryOptions.carrier'      => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.1.deliveryOptions.deliveryType' => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
            'data.orders.1.deliveryOptions.labelAmount'  => 1,
            'data.orders.1.deliveryOptions.packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.1.label'                        => null,
        ])
        ->and($response->getStatusCode())
        ->toBe(200);
});
