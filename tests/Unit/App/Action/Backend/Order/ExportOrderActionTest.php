<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrdersResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApiService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesApiMock());

it('exports order without customer information if setting is false', function (
    bool  $share,
    bool  $orderMode,
    array $orders
) {
    MockPdkFactory::create([
        ApiServiceInterface::class         => autowire(MockApiService::class)->constructor(
            $orderMode ? new ExamplePostOrdersResponse() : new ExamplePostShipmentsResponse()
        ),
        PdkOrderRepositoryInterface::class => autowire(MockPdkOrderRepository::class)->constructor($orders),
        SettingsRepositoryInterface::class => autowire(MockSettingsRepository::class)->constructor([
            GeneralSettings::ID => [
                GeneralSettings::ORDER_MODE                 => $orderMode,
                GeneralSettings::SHARE_CUSTOMER_INFORMATION => $share,
            ],
        ]),
    ]);

    Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => Arr::pluck($orders, 'externalIdentifier'),
    ]);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api         = Pdk::get(ApiServiceInterface::class);
    $lastRequest = $api->getMock()
        ->getLastRequest();

    if (! $lastRequest) {
        throw new RuntimeException('No request was made.');
    }

    $content = json_decode(
        $lastRequest->getBody()
            ->getContents(),
        true
    );

    $postedAddress = Arr::get(
        $content,
        $orderMode
            ? 'data.orders.0.invoice_address'
            : 'data.shipments.0.recipient'
    );

    expect($postedAddress)->toBeArray();

    if ($share) {
        expect(Arr::only($postedAddress, ['email', 'phone']))
            ->each->toBeString();
    } else {
        expect(Arr::only($postedAddress, ['email', 'phone']))
            ->each->toBeNull();
    }
})
    ->with([
        'share'        => [
            'share'     => true,
            'orderMode' => false,
        ],
        'do not share' => [
            'share'     => false,
            'orderMode' => false,
        ],

        'order mode: share'        => [
            'share'     => true,
            'orderMode' => true,
        ],
        'order mode: do not share' => [
            'share'     => false,
            'orderMode' => true,
        ],
    ])
    ->with('pdkOrdersDomestic');

it('exports entire order', function (array $orders) {
    MockPdkFactory::create([
        ApiServiceInterface::class         => autowire(MockApiService::class)->constructor([
            new ExamplePostOrdersResponse(),
            new ExamplePostOrderNotesResponse(),
        ]),
        PdkOrderRepositoryInterface::class => autowire(MockPdkOrderRepository::class)->constructor($orders),
        SettingsRepositoryInterface::class => autowire(MockSettingsRepository::class)->constructor([
            GeneralSettings::ID => [
                GeneralSettings::ORDER_MODE => true,
            ],
        ]),
    ]);

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => Arr::pluck($orders, 'externalIdentifier'),
    ]);

    $content = json_decode($response->getContent(), true);

    $responseOrders    = $content['data']['orders'];
    $responseShipments = Arr::pluck($responseOrders, 'shipments');

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and(Arr::dot($content))
        ->and($responseOrders)
        ->toHaveLength(count($orders))
        ->and($responseShipments)->each->toHaveLength(0)
        ->and($response->getStatusCode())
        ->toBe(200);
})->with('pdkOrdersDomestic');
