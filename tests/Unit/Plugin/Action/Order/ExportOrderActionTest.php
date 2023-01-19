<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Plugin\Action\Order;

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Repository\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Settings\Repository\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
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

it('exports entire order', function (array $orders) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new ExamplePostShipmentsResponse());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository $settingsRepository */
    $settingsRepository = Pdk::get(SettingsRepositoryInterface::class);
    $settingsRepository->set(
        new Settings([
            GeneralSettings::ID => [
                GeneralSettings::ORDER_MODE => true,
            ],
        ])
    );

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $orderRepository */
    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);
    $orderRepository->add(...(new PdkOrderCollection($orders))->all());

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => Arr::pluck($orders, 'externalIdentifier'),
    ]);

    if (! $response) {
        throw new RuntimeException('Response is empty');
    }

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
