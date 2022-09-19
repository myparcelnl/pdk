<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\PdkActions;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsLinkV2Response;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use MyParcelNL\Sdk\src\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use function DI\autowire;

beforeEach(function () {
    $this->pdk = PdkFactory::create(
        MockPdkConfig::create([
            AbstractPdkOrderRepository::class => autowire(MockPdkOrderRepository::class),
        ])
    );

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $orderRepository */
    $orderRepository = $this->pdk->get(AbstractPdkOrderRepository::class);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $this->pdk->get(ApiServiceInterface::class);

    $this->orderRepository = $orderRepository;
    $this->mock            = $api->getMock();
});

afterEach(function () {
    $this->mock->reset();
});

it('exports order', function () {
    $this->mock->append(new ExamplePostShipmentsResponse([['id' => 30011], ['id' => 30012]]));
    $this->orderRepository->add(
        new PdkOrder(
            [
                'externalIdentifier' => '245',
                'deliveryOptions'    => [
                    'carrier' => CarrierOptions::CARRIER_POSTNL_NAME,
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

    $response = $this->pdk->execute(PdkActions::EXPORT_ORDER, [
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
            /**
             * 245
             */
            'data.orders.0.externalIdentifier'                       => '245',
            'data.orders.0.deliveryOptions.carrier'                  => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.0.deliveryOptions.labelAmount'              => 1,
            'data.orders.0.deliveryOptions.packageType'              => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.0.label'                                    => null,
            'data.orders.0.shipments.0.deliveryOptions.carrier'      => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.0.shipments.0.deliveryOptions.deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            'data.orders.0.shipments.0.deliveryOptions.packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.0.shipments.0.id'                           => 30011,
            'data.orders.0.shipments.0.orderId'                      => '245',

            'data.orders.1.externalIdentifier'                       => '247',
            'data.orders.1.deliveryOptions.carrier'                  => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.1.deliveryOptions.deliveryType'             => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
            'data.orders.1.deliveryOptions.labelAmount'              => 1,
            'data.orders.1.deliveryOptions.packageType'              => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.1.label'                                    => null,
            'data.orders.1.shipments.0.deliveryOptions.carrier'      => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.1.shipments.0.deliveryOptions.deliveryType' => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
            'data.orders.1.shipments.0.deliveryOptions.labelAmount'  => 1,
            'data.orders.1.shipments.0.deliveryOptions.packageType'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.1.shipments.0.id'                           => 30012,
            'data.orders.1.shipments.0.orderId'                      => '247',
        ])
        ->and($response->getStatusCode())
        ->toBe(200);
});

it('exports and prints order', function () {
    $this->mock->append(
        new ExamplePostShipmentsResponse([
            ['id' => 30321, 'reference_identifier' => '263'],
            ['id' => 30322, 'reference_identifier' => '264'],
        ]),
        new ExampleGetShipmentLabelsLinkV2Response()
    );

    $this->orderRepository->add(
        new PdkOrder(
            [
                'externalIdentifier' => '263',
                'deliveryOptions'    => [
                    'carrier' => CarrierOptions::CARRIER_INSTABOX_NAME,
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

    $response = $this->pdk->execute(PdkActions::EXPORT_AND_PRINT_ORDER, [
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
            /**
             * 245
             */
            'data.orders.0.externalIdentifier'                       => '263',
            'data.orders.0.deliveryOptions.carrier'                  => CarrierOptions::CARRIER_INSTABOX_NAME,
            'data.orders.0.deliveryOptions.labelAmount'              => 1,
            'data.orders.0.deliveryOptions.packageType'              => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.0.label.link'                               => 'API/pdfs/label_hash',
            'data.orders.0.label.pdf'                                => null,
            'data.orders.0.shipments.0.deliveryOptions.carrier'      => CarrierOptions::CARRIER_INSTABOX_NAME,
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
            'data.orders.1.label.link'                                            => 'API/pdfs/label_hash',
            'data.orders.1.label.pdf'                                             => null,
            'data.orders.1.shipments.0.deliveryOptions.carrier'                   => CarrierOptions::CARRIER_POSTNL_NAME,
            'data.orders.1.shipments.0.deliveryOptions.deliveryType'              => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
            'data.orders.1.shipments.0.deliveryOptions.labelAmount'               => 1,
            'data.orders.1.shipments.0.deliveryOptions.packageType'               => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'data.orders.1.shipments.0.deliveryOptions.shipmentOptions.signature' => true,
            'data.orders.1.shipments.0.id'                                        => 30322,
            'data.orders.1.shipments.0.orderId'                                   => '264',
        ])
        ->and($response->getStatusCode())
        ->toBe(200);
});
