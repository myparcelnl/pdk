<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use Symfony\Component\HttpFoundation\Response;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesApiMock());

it('exports order to shipment', function ($orders) {
    MockPdkFactory::create([
        PdkOrderRepositoryInterface::class => autowire(MockPdkOrderRepository::class)->constructor($orders),
    ]);

    MockApi::enqueue(new ExamplePostShipmentsResponse());

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => Arr::pluck($orders, 'externalIdentifier'),
    ]);

    $content = json_decode($response->getContent(), true);

    $responseOrders    = $content['data']['orders'];
    $responseShipments = Arr::pluck($responseOrders, 'shipments');

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($responseOrders)
        ->toHaveLength(count($orders))
        // Each shipment should have an ID
        ->and($responseShipments)->each->toHaveLength(1)
        // Each shipment should have an ID
        ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt()
        ->and($response->getStatusCode())
        ->toBe(200);
})->with('pdkOrdersDomestic');

