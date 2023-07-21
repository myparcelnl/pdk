<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

it('creates a valid order note collection from api data', function (?string $fulfilmentId, array $input) {
    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderNotesRepository $repository */
    $repository = Pdk::get(OrderNotesRepository::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new ExamplePostOrderNotesResponse());

    $savedOrderNotes = $repository->postOrderNotes($fulfilmentId, new OrderNoteCollection($input));

    assertMatchesJsonSnapshot(json_encode($savedOrderNotes->toArray()));
})->with([
    'single note' => [
        'fulfilmentId' => '657718',
        'input'        => [
            [
                'note'      => 'This is a note',
                'author'    => 'webshop',
                'createdAt' => '2023-01-01 12:00:00',
                'updatedAt' => '2023-01-01 12:00:00',
            ],
        ],
    ],

    'multiple notes' => [
        'fulfilmentId' => '12345678',
        'input'        => [
            [
                'note'      => 'This is a note',
                'author'    => 'customer',
                'createdAt' => '2023-01-01 12:00:00',
                'updatedAt' => '2023-01-01 12:00:00',
            ],
            [
                'note'      => 'This is another note',
                'author'    => 'webshop',
                'createdAt' => '2023-01-01 12:00:00',
                'updatedAt' => '2023-01-01 12:00:00',
            ],
        ],
    ],
]);
