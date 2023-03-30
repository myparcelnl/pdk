<?php
/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Plugin\Action\Order;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Plugin\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsLinkV2Response;
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

it('prints order', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $orderRepository */
    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);

    $api->getMock()
        ->append(
            new ExamplePostIdsResponse([
                ['id' => 30321, 'reference_identifier' => '263'],
                ['id' => 30322, 'reference_identifier' => '264'],
            ]),
            new ExampleGetShipmentLabelsLinkV2Response()
        );

    $orderRepository->add(
        new PdkOrder(['externalIdentifier' => '263']),
        new PdkOrder(['externalIdentifier' => '264'])
    );

    $response = Actions::execute(PdkBackendActions::PRINT_ORDERS, [
        'orderIds' => ['263', '264'],
    ]);

    if (! $response) {
        throw new RuntimeException('Response is empty');
    }

    $content = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($content)
        ->toEqual([
            'data' => [
                'pdfs' => [
                    'data' => 'eyJkYXRhIjp7ImlkcyI6W3siaWQiOjMwMzIxLCJyZWZlcmVuY2VfaWRlbnRpZmllciI6IjI2MyJ9LHsiaWQiOjMwMzIyLCJyZWZlcmVuY2VfaWRlbnRpZmllciI6IjI2NCJ9XX19'
                ],
            ],
        ])
        ->and($response->getStatusCode())
        ->toBe(200);
});
