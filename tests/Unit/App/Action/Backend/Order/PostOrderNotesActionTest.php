<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesEachMockPdkInstance([
        SettingsRepositoryInterface::class => autowire(MockSettingsRepository::class)->constructor([
            GeneralSettings::ID => [
                GeneralSettings::ORDER_MODE => true,
            ],
        ]),
    ]),
    new UsesApiMock()
);

it('posts order notes if order has notes', function (array $orders) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $orderRepository */
    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = Pdk::get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostOrderNotesResponse());

    $orderCollection = new PdkOrderCollection($orders);
    $orderRepository->add(...$orderCollection->all());

    Actions::execute(PdkBackendActions::POST_ORDER_NOTES, [
        'OVERRIDE' => true,
        'orderIds' => Arr::pluck($orders, 'externalIdentifier'),
    ]);

    $request = $mock->getLastRequest();

    if ($orderCollection->contains('apiIdentifier', '==', null)) {
        expect($request)->toBeNull();
        return;
    }

    expect($request)->toBeTruthy();
})->with([
    'single order' => function () {
        return [
            new PdkOrder([
                'apiIdentifier'      => '90001',
                'externalIdentifier' => '243',
                'shippingAddress'    => [
                    'cc'          => 'NL',
                    'city'        => 'Hoofddorp',
                    'person'      => 'Felicia Parcel',
                    'postal_code' => '2132 JE',
                    'address1'    => 'Antareslaan 31',
                ],
                'deliveryOptions'    => [
                    'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                    'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                    'shipmentOptions' => [
                        'signature' => true,
                    ],
                ],
                'notes'              => [
                    [
                        'author'    => OrderNote::AUTHOR_WEBSHOP,
                        'note'      => 'test note',
                        'createdAt' => '2023-01-01 12:00:00',
                        'updatedAt' => '2023-01-01 12:00:00',
                    ],
                    [
                        'author'    => OrderNote::AUTHOR_CUSTOMER,
                        'note'      => 'hello',
                        'createdAt' => '2023-01-01 12:00:00',
                        'updatedAt' => '2023-01-02 12:00:00',
                    ],
                ],
            ]),
        ];
    },

    'two orders where only one has notes' => function () {
        return [
            new PdkOrder([
                'apiIdentifier'      => '90002',
                'externalIdentifier' => '245',
                'shippingAddress'    => [
                    'cc'         => CountryCodes::CC_NL,
                    'address1'   => 'Pietjestraat 35',
                    'postalCode' => '2771BW',
                    'city'       => 'Bikinibroek',
                ],
            ]),

            new PdkOrder([
                'apiIdentifier'      => '90003',
                'externalIdentifier' => '247',
                'shippingAddress'    => [
                    'cc'          => 'NL',
                    'city'        => 'Hoofddorp',
                    'person'      => 'Felicia Parcel',
                    'postal_code' => '2132 JE',
                    'address1'    => 'Antareslaan 31',
                ],
                'deliveryOptions'    => [
                    'carrier'      => Carrier::CARRIER_POSTNL_NAME,
                    'deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                ],
                'notes'              => [
                    [
                        'author'    => OrderNote::AUTHOR_CUSTOMER,
                        'note'      => 'test note from customer',
                        'createdAt' => '2023-01-01 12:00:00',
                        'updatedAt' => '2023-01-01 18:00:00',
                    ],
                ],
            ]),
        ];
    },

    'order without api identifier' => function () {
        return [
            new PdkOrder([
                'externalIdentifier' => '248',
                'shippingAddress'    => [
                    'cc'          => 'NL',
                    'city'        => 'Hoofddorp',
                    'person'      => 'Felicia Parcel',
                    'postal_code' => '2132 JE',
                    'address1'    => 'Antareslaan 31',
                ],
                'notes'              => [
                    [
                        'author'    => OrderNote::AUTHOR_CUSTOMER,
                        'note'      => 'hello',
                        'createdAt' => '2023-01-01 12:00:00',
                        'updatedAt' => '2023-01-02 12:00:00',
                    ],
                ],
            ]),
        ];
    },
]);
