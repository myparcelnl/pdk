<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Fulfilment\Repository;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderNotesRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

it('creates a valid order note collection from api data', function (array $input, ?string $fulfilmentId, $result) {
    $pdk = PdkFactory::create(MockPdkConfig::create());
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostOrderNotesResponse(), new ExamplePostOrderNotesResponse());

    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderNotesRepository $repository */
    $repository      = $pdk->get(OrderNotesRepository::class);
    $savedOrderNotes = $repository->postOrderNotes(new OrderNoteCollection($input), $fulfilmentId);

    expect($savedOrderNotes)
        ->toEqual($result);

    assertMatchesJsonSnapshot(json_encode($savedOrderNotes ? $savedOrderNotes->toArray() : null));
})->with([
    'multiple notes with existing fulfilment id'     => [
        'input'        => [
            [
                'note'   => 'This is a note',
                'author' => 'customer',
            ],
            [
                'note'   => 'This is another note',
                'author' => 'webshop',
            ],
        ],
        'fulfilmentId' => '12345678',
        'result'       => new OrderNoteCollection([
            [
                'note'   => 'This is a note',
                'author' => 'customer',
            ],
        ]),
    ],
    'multiple notes with non-existent fulfilment id' => [
        'input'        => [
            [
                'note'   => 'This is a note',
                'author' => 'customer',
            ],
            [
                'note'   => 'This is another note',
                'author' => 'webshop',
            ],
        ],
        'fulfilmentId' => null,
        'result'       => null,
    ],
]);
