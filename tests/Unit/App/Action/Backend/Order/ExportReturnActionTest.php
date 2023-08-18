<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Facade\MockApi;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Mock\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\factory;

beforeEach(function () {
    factory(PdkOrder::class)
        ->withExternalIdentifier('701')
        ->withShipments([
            factory(Shipment::class)
                ->withId(100001)
                ->withReferenceIdentifier('1'),

            factory(Shipment::class)
                ->withId(100002)
                ->withReferenceIdentifier('2')
                ->withDeliveryOptions([
                    'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                    'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                    'shipmentOptions' => [
                        'signature' => true,
                    ],
                ]),
        ])
        ->store();

    factory(PdkOrder::class)
        ->withExternalIdentifier('247')
        ->withDeliveryOptions([
            'carrier'      => Carrier::CARRIER_POSTNL_NAME,
            'deliveryType' => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
        ])
        ->store();
});

it('exports return', function () {
    MockApi::enqueue(
        new ExamplePostIdsResponse([['id' => 30011], ['id' => 30012]]),
        new ExampleGetShipmentsResponse()
    );

    $response = Actions::execute(PdkBackendActions::EXPORT_RETURN, [
        'orderIds' => ['701', '247'],
    ]);

    $content = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and(Arr::dot($content))
        ->toHaveKeysAndValues([
            'data.orders.0.externalIdentifier'           => '701',
            'data.orders.0.deliveryOptions.carrier.name' => Carrier::CARRIER_POSTNL_NAME,
            'data.orders.0.deliveryOptions.labelAmount'  => 1,
            'data.orders.0.deliveryOptions.packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.1.externalIdentifier'           => '247',
            'data.orders.1.deliveryOptions.carrier.name' => Carrier::CARRIER_POSTNL_NAME,
            'data.orders.1.deliveryOptions.deliveryType' => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
            'data.orders.1.deliveryOptions.labelAmount'  => 1,
            'data.orders.1.deliveryOptions.packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ])
        ->and($response->getStatusCode())
        ->toBe(200);
});
