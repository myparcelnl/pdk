<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Api\Contract\PdkActionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsLinkV2Response;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsPdfResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApiService;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesApiMock());

beforeEach(function () {
    factory(PdkOrderCollection::class)
        ->push(
            factory(PdkOrder::class)
                ->withExternalIdentifier('263')
                ->withShipments(
                    factory(ShipmentCollection::class)->push(
                        factory(Shipment::class)->withId(30321),
                        factory(Shipment::class)->withId(30322)
                    )
                ),

            // Not printed
            factory(PdkOrder::class)
                ->withExternalIdentifier('264')
                ->withShipments(
                    factory(ShipmentCollection::class)->push(
                        factory(Shipment::class)->withId(30456),
                        factory(Shipment::class)->withId(30457)
                    )
                ),

            // Without shipments
            factory(PdkOrder::class)
                ->withExternalIdentifier('265')
                ->withShipments([]),

            factory(PdkOrder::class)
                ->withExternalIdentifier('266')
                ->withShipments(
                    factory(ShipmentCollection::class)->push(
                        factory(Shipment::class)->withId(30876),
                        factory(Shipment::class)->withId(30877)
                    )
                )
        )
        ->store();
});

it('prints order as a4 pdf', function (array $settings, string $queryString) {
    factory(LabelSettings::class)
        ->with($settings)
        ->store();

    if ($settings[LabelSettings::OUTPUT] === LabelSettings::OUTPUT_DOWNLOAD) {
        MockApi::enqueue(new ExampleGetShipmentLabelsLinkV2Response());
    } else {
        MockApi::enqueue(new ExampleGetShipmentLabelsPdfResponse());
    }

    $response = Actions::execute(PdkBackendActions::PRINT_ORDERS, [
        'orderIds' => ['263', '265', '266'],
    ]);

    $content = json_decode($response->getContent(), true);

    expect($response->getStatusCode())
        ->toBe(200)
        ->and($content)
        ->toHaveKey('data');

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkActionsService $actions */
    $actions = Pdk::get(PdkActionsServiceInterface::class);
    $calls   = $actions->getCalls();

    expect($calls->toArray())->toEqual([
        [
            'action'     => PdkBackendActions::PRINT_ORDERS,
            'parameters' => [
                'orderIds' => ['263', '265', '266'],
            ],
        ],
        [
            'action'     => PdkBackendActions::UPDATE_ORDER_STATUS,
            'parameters' => [
                'orderIds' => ['263', '265', '266'],
                'setting'  => 'statusOnLabelCreate',
            ],
        ],
    ]);

    $apiService = Pdk::get(MockApiService::class);

    $lastRequest = $apiService->ensureLastRequest();
    $uri         = $lastRequest->getUri();

    expect($uri->getPath())
        ->toBe('API/shipment_labels/30321;30322;30876;30877')
        ->and($uri->getQuery())
        ->toBe($queryString);
})->with([
    'default settings' => [[], 'format=A4&positions=1%3B2%3B3%3B4'],

    'a4 with custom position' => [
        'settings'    => [
            LabelSettings::FORMAT   => LabelSettings::FORMAT_A4,
            LabelSettings::POSITION => [2, 4],
        ],
        'queryString' => 'format=A4&positions=2%3B4',
    ],

    'a6' => [
        'settings'    => [
            LabelSettings::FORMAT => LabelSettings::FORMAT_A6,
        ],
        'queryString' => 'format=A6&positions=1%3B2%3B3%3B4',
    ],

    'a6, output = download' => [
        'settings'    => [
            LabelSettings::FORMAT => LabelSettings::FORMAT_A6,
            LabelSettings::OUTPUT => LabelSettings::OUTPUT_DOWNLOAD,
        ],
        'queryString' => 'format=A6&positions=1%3B2%3B3%3B4',
    ],

    'a6, output = download, position = 1,3' => [
        'settings'    => [
            LabelSettings::FORMAT   => LabelSettings::FORMAT_A6,
            LabelSettings::OUTPUT   => LabelSettings::OUTPUT_DOWNLOAD,
            LabelSettings::POSITION => [1, 3],
        ],
        'queryString' => 'format=A6&positions=1%3B3',
    ],

    'a4, output = download' => [
        'settings'    => [
            LabelSettings::FORMAT => LabelSettings::FORMAT_A4,
            LabelSettings::OUTPUT => LabelSettings::OUTPUT_DOWNLOAD,
        ],
        'queryString' => 'format=A4&positions=1%3B2%3B3%3B4',

    ],
]);

