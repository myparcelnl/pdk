<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollectionFactory;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

it('exports return', function (PdkOrderCollectionFactory $ordersFactory) {
    (new FactoryCollection([
        $ordersFactory,
    ]))->store();

    MockApi::enqueue(
        new ExamplePostIdsResponse([['id' => 30011], ['id' => 30012]]),
        new ExampleGetShipmentsResponse()
    );

    $response = Actions::execute(PdkBackendActions::EXPORT_RETURN, [
        'orderIds' => ['701', '247'],
    ]);

    assertMatchesJsonSnapshot($response->getContent());
    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())
        ->toBe(200);
})->with([
    'simple orders'          => [
        function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withExternalIdentifier('701')
                    ->withShipments([
                        [
                            'id'                  => 100001,
                            'referenceIdentifier' => '1',
                        ],
                        [
                            'id'                  => 100002,
                            'referenceIdentifier' => '2',
                            'deliveryOptions'     => [
                                'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                                'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                                'shipmentOptions' => [
                                    'signature' => true,
                                ],
                            ],
                        ],
                    ]),
                factory(PdkOrder::class)
                    ->withExternalIdentifier('247')
                    ->withDeliveryOptions([
                        'carrier'      => Carrier::CARRIER_POSTNL_NAME,
                        'deliveryType' => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                    ])
            );
        },
    ],
    'insurance'              => [
        function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withExternalIdentifier('701')
                    ->withShipments([
                        [
                            'id'                  => 100001,
                            'referenceIdentifier' => '1',
                            'deliveryOptions'     => [
                                'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                                'shipmentOptions' => [
                                    'insurance' => 0,
                                ],
                            ],
                        ],
                        [
                            'id'                  => 100002,
                            'referenceIdentifier' => '2',
                            'deliveryOptions'     => [
                                'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                                'shipmentOptions' => [
                                    'insurance' => 500,
                                ],
                            ],
                        ],
                    ])
            );
        },
    ],
    'no return capabilities' => [
        function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withExternalIdentifier('701')
                    ->withShipments([
                        [
                            'id'                  => 100001,
                            'referenceIdentifier' => '1',
                            'deliveryOptions'     => [
                                'carrier'         => Carrier::CARRIER_DHL_EUROPLUS_NAME,
                                'shipmentOptions' => [
                                    'insurance' => 0,
                                ],
                            ],
                        ],
                        [
                            'id'                  => 100002,
                            'referenceIdentifier' => '2',
                            'deliveryOptions'     => [
                                'carrier'         => Carrier::CARRIER_DHL_EUROPLUS_NAME,
                                'shipmentOptions' => [
                                    'insurance' => 500,
                                ],
                            ],
                        ],
                    ])
            );
        },
    ],
]);
