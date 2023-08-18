<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Facade\MockApi;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetShipmentLabelsLinkV2Response;
use MyParcelNL\Pdk\Mock\Api\Response\ExamplePostIdsResponse;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\factory;

beforeEach(function () {
    factory(PdkOrder::class)
        ->withExternalIdentifier('263')
        ->store();

    factory(PdkOrder::class)
        ->withExternalIdentifier('264')
        ->store();
});

it('prints order', function () {
    MockApi::enqueue(
        new ExamplePostIdsResponse([
            ['id' => 30321, 'reference_identifier' => '263'],
            ['id' => 30322, 'reference_identifier' => '264'],
        ]),
        new ExampleGetShipmentLabelsLinkV2Response()
    );

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
