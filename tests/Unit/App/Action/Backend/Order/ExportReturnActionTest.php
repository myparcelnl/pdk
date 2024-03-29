<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollectionFactory;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

it('exports return', function (PdkOrderCollectionFactory $ordersFactory) {
    $ordersFactory->store();

    MockApi::enqueue(
        new ExamplePostIdsResponse([['id' => 30011], ['id' => 30012]]),
        new ExampleGetShipmentsResponse()
    );

    $response = Actions::execute(PdkBackendActions::EXPORT_RETURN, [
        'orderIds' => ['701', '247'],
    ]);

    $content = json_decode($response->getContent(), true);

    $content['data']['orders'] = array_map(function (array $order) {
        return array_replace($order, [
            'shipments' => array_map(function (array $shipment) {
                Arr::forget($shipment, [
                    'updated',
                    'carrier.capabilities',
                    'carrier.returnCapabilities',
                    'deliveryOptions.carrier.capabilities',
                    'deliveryOptions.carrier.returnCapabilities',
                ]);

                return $shipment;
            }, $order['shipments'] ?? []),
        ]);
    }, $content['data']['orders']);

    assertMatchesJsonSnapshot(json_encode($content));
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
                        factory(Shipment::class)
                            ->withId(100001)
                            ->withReferenceIdentifier('1'),
                        factory(Shipment::class)
                            ->withId(100002)
                            ->withReferenceIdentifier('2')
                            ->withDeliveryOptions(
                                factory(DeliveryOptions::class)
                                    ->withCarrier(Carrier::CARRIER_POSTNL_NAME)
                                    ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_MORNING_NAME)
                                    ->withShipmentOptions(factory(ShipmentOptions::class)->withSignature(1))
                            ),
                    ]),
                factory(PdkOrder::class)
                    ->withExternalIdentifier('247')
                    ->withDeliveryOptions(
                        factory(DeliveryOptions::class)
                            ->withCarrier(Carrier::CARRIER_POSTNL_NAME)
                            ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
                    )
            );
        },
    ],
    'insurance'              => [
        function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withExternalIdentifier('701')
                    ->withShipments([
                        factory(Shipment::class)
                            ->withId(100001)
                            ->withReferenceIdentifier('1')
                            ->withDeliveryOptions(
                                factory(DeliveryOptions::class)
                                    ->withCarrier(Carrier::CARRIER_POSTNL_NAME)
                                    ->withShipmentOptions(
                                        factory(ShipmentOptions::class)
                                            ->withInsurance(0)
                                    )
                            ),
                        factory(Shipment::class)
                            ->withId(100002)
                            ->withReferenceIdentifier('2')
                            ->withDeliveryOptions(
                                factory(DeliveryOptions::class)
                                    ->withCarrier(Carrier::CARRIER_POSTNL_NAME)
                                    ->withShipmentOptions(
                                        factory(ShipmentOptions::class)
                                            ->withInsurance(500)
                                    )
                            ),
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
                        factory(Shipment::class)
                            ->withId(100001)
                            ->withReferenceIdentifier('1')
                            ->withDeliveryOptions(
                                factory(DeliveryOptions::class)
                                    ->withCarrier(Carrier::CARRIER_DHL_EUROPLUS_NAME)
                                    ->withShipmentOptions(
                                        factory(ShipmentOptions::class)
                                            ->withInsurance(0)
                                    )
                            ),
                        factory(Shipment::class)
                            ->withId(100002)
                            ->withReferenceIdentifier('2')
                            ->withDeliveryOptions(
                                factory(DeliveryOptions::class)
                                    ->withCarrier(Carrier::CARRIER_DHL_EUROPLUS_NAME)
                                    ->withShipmentOptions(
                                        factory(ShipmentOptions::class)
                                            ->withInsurance(500)
                                    )
                            ),
                    ])
            );
        },
    ],
]);
