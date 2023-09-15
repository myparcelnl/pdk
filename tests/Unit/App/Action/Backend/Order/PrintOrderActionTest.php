<?php
/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsLinkV2Response;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Response;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance([
        PdkOrderRepositoryInterface::class => autowire(MockPdkOrderRepository::class)->constructor([
            ['externalIdentifier' => '263'],
            ['externalIdentifier' => '264'],
        ]),
    ]),
    new UsesApiMock()
);

it('prints order', function () {
    MockApi::enqueue(
        new ExamplePostIdsResponse([
            ['id' => 30321, 'reference_identifier' => '263'],
            ['id' => 30322, 'reference_identifier' => '264'],
        ]),
        new ExampleGetShipmentLabelsLinkV2Response()
    );

    // Set this option to prevent error
    factory(OrderSettings::class)
        ->withStatusOnLabelCreate('test')
        ->store();

    $response = Actions::execute(PdkBackendActions::PRINT_ORDERS, [
        'orderIds' => ['263', '264'],
    ]);

    $content = json_decode($response->getContent(), true);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($content)
        ->toEqual([
            'data' => [
                'pdfs' => [
                    'data' => 'eyJkYXRhIjp7ImlkcyI6W3siaWQiOjMwMzIxLCJyZWZlcmVuY2VfaWRlbnRpZmllciI6IjI2MyJ9LHsiaWQiOjMwMzIyLCJyZWZlcmVuY2VfaWRlbnRpZmllciI6IjI2NCJ9XX19',
                ],
            ],
        ])
        ->and($response->getStatusCode())
        ->toBe(200);
});
