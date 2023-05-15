<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Plugin\Action\Order;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance([
        PdkOrderRepositoryInterface::class => autowire(MockPdkOrderRepository::class),
    ]),
    new UsesApiMock()
);

it('exports return', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = Pdk::get(ApiServiceInterface::class);
    $mock = $api->getMock();

    $mock->append(new ExamplePostIdsResponse([['id' => 30011], ['id' => 30012]]));
    $mock->append(new ExampleGetShipmentsResponse());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $orderRepository */
    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);

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
                            'carrier'         => Carrier::CARRIER_POSTNL_NAME,
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
                    'carrier'      => Carrier::CARRIER_POSTNL_NAME,
                    'deliveryType' => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                ],
            ]
        )
    );

    $response = Actions::execute(PdkBackendActions::EXPORT_RETURN, [
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
            'data.orders.0.deliveryOptions.carrier'      => Carrier::CARRIER_POSTNL_NAME,
            'data.orders.0.deliveryOptions.labelAmount'  => 1,
            'data.orders.0.deliveryOptions.packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.1.externalIdentifier'           => '247',
            'data.orders.1.deliveryOptions.carrier'      => Carrier::CARRIER_POSTNL_NAME,
            'data.orders.1.deliveryOptions.deliveryType' => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
            'data.orders.1.deliveryOptions.labelAmount'  => 1,
            'data.orders.1.deliveryOptions.packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ])
        ->and($response->getStatusCode())
        ->toBe(200);
})->with('pdkOrdersDomestic');
