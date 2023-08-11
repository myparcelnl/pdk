<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesApiMock());

it('posts order notes if order has notes', function (PdkOrderCollection $orders, PdkOrderNoteCollection $notes) {
    MockPdkFactory::create([
        SettingsRepositoryInterface::class => autowire(MockSettingsRepository::class)->constructor([
            GeneralSettings::ID => [
                GeneralSettings::ORDER_MODE => true,
            ],
        ]),
        PdkOrderRepositoryInterface::class => autowire(MockPdkOrderRepository::class)->constructor($orders),
    ]);

    MockApi::enqueue(new ExamplePostOrderNotesResponse());

    $orderNoteRepository = Pdk::get(PdkOrderNoteRepositoryInterface::class);
    $notes->each(function (PdkOrderNote $note) use ($orderNoteRepository) {
        $orderNoteRepository->add($note);
    });

    $orderCollection = new Collection($orders);

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
    'single order' => [
        new PdkOrderCollection([
            [
                'apiIdentifier'      => '90001',
                'externalIdentifier' => '243',
                'shippingAddress'    => [
                    'cc'          => CountryCodes::CC_NL,
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
            ],
        ]),
        new PdkOrderNoteCollection([
            [
                'author'          => OrderNote::AUTHOR_WEBSHOP,
                'note'            => 'test note',
                'orderIdentifier' => '243',
                'createdAt'       => '2023-01-01 12:00:00',
                'updatedAt'       => '2023-01-01 12:00:00',
            ],
            [
                'author'          => OrderNote::AUTHOR_CUSTOMER,
                'note'            => 'hello',
                'orderIdentifier' => '243',
                'createdAt'       => '2023-01-01 12:00:00',
                'updatedAt'       => '2023-01-02 12:00:00',
            ],
        ]),

    ],

    'two orders where only one has notes' => [
        new PdkOrderCollection([
            [
                'apiIdentifier'      => '90002',
                'externalIdentifier' => '245',
                'shippingAddress'    => [
                    'cc'         => CountryCodes::CC_NL,
                    'address1'   => 'Pietjestraat 35',
                    'postalCode' => '2771BW',
                    'city'       => 'Bikinibroek',
                ],
                'deliveryOptions'    => [],
            ],
            [
                'apiIdentifier'      => '90003',
                'externalIdentifier' => '247',
                'shippingAddress'    => [
                    'cc'          => CountryCodes::CC_NL,
                    'city'        => 'Hoofddorp',
                    'person'      => 'Felicia Parcel',
                    'postal_code' => '2132 JE',
                    'address1'    => 'Antareslaan 31',
                ],
                'deliveryOptions'    => [
                    'carrier'      => Carrier::CARRIER_POSTNL_NAME,
                    'deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                ],
            ],
        ]),
        new PdkOrderNoteCollection([
            [
                'author'          => OrderNote::AUTHOR_CUSTOMER,
                'note'            => 'test note from customer',
                'orderIdentifier' => '247',
                'createdAt'       => '2023-01-01 12:00:00',
                'updatedAt'       => '2023-01-01 18:00:00',
            ],
        ]),
    ],

    'order without api identifier' => [
        new PdkOrderCollection([
            [
                'externalIdentifier' => '248',
                'shippingAddress'    => [
                    'cc'          => CountryCodes::CC_NL,
                    'city'        => 'Hoofddorp',
                    'person'      => 'Felicia Parcel',
                    'postal_code' => '2132 JE',
                    'address1'    => 'Antareslaan 31',
                ],
                'deliveryOptions'    => [],
            ],
        ]),
        new PdkOrderNoteCollection([
            [
                'author'          => OrderNote::AUTHOR_CUSTOMER,
                'note'            => 'hello',
                'orderIdentifier' => '248',
                'createdAt'       => '2023-01-01 12:00:00',
                'updatedAt'       => '2023-01-02 12:00:00',
            ],
        ]),
    ],
]);
