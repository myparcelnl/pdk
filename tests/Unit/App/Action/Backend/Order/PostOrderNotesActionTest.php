<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Facade\MockApi;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;
use MyParcelNL\Pdk\Mock\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use function MyParcelNL\Pdk\Tests\factory;

beforeEach(function () {
    factory(GeneralSettings::class)
        ->withOrderMode(true)
        ->store();
});

it('posts order notes if order has notes', function (array $factories) {
    $orderCollectionFactory = factory(OrderCollection::class)
        ->push(...$factories)
        ->store();

    MockApi::enqueue(new ExamplePostOrderNotesResponse());

    $orderCollection = new Collection(
        $orderCollectionFactory
            ->make()
            ->all()
    );

    Actions::execute(PdkBackendActions::POST_ORDER_NOTES, [
        'OVERRIDE' => true,
        'orderIds' => $orderCollection
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    $request = MockApi::getLastRequest();

    if ($orderCollection->contains('apiIdentifier', '==', null)) {
        expect($request)->toBeNull();
        return;
    }

    expect($request)->toBeTruthy();
})->with([
    'single order' => function () {
        return [
            factory(PdkOrder::class)->with([
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
            factory(PdkOrder::class)->with([
                'apiIdentifier'      => '90002',
                'externalIdentifier' => '245',
                'shippingAddress'    => [
                    'cc'         => CountryCodes::CC_NL,
                    'address1'   => 'Pietjestraat 35',
                    'postalCode' => '2771BW',
                    'city'       => 'Bikinibroek',
                ],
            ]),

            factory(PdkOrder::class)->with([
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
            factory(PdkOrder::class)
                ->fromScratch()
                ->with([
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
