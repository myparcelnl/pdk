<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollectionFactory;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('creates a valid order note collection from api data', function (
    ?string                       $fulfilmentId,
    PdkOrderNoteCollectionFactory $factory
) {
    $orderNoteCollection = $factory->make();
    $collection          = new Collection($orderNoteCollection);

    MockApi::enqueue(
        new ExamplePostOrderNotesResponse(
            $collection
                ->map(function (PdkOrderNote $note) use ($fulfilmentId) {
                    return [
                        'uuid'    => $fulfilmentId,
                        'author'  => $note->author,
                        'note'    => $note->note,
                        'created' => $note->createdAt->format(Pdk::get('defaultDateFormat')),
                        'updated' => $note->createdAt->format(Pdk::get('defaultDateFormat')),
                    ];
                })
                ->toArray()
        )
    );

    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderNotesRepository $repository */
    $repository = Pdk::get(OrderNotesRepository::class);

    $savedOrderNotes = $repository->postOrderNotes($fulfilmentId, $orderNoteCollection);

    $expected = $collection
        ->map(function (PdkOrderNote $pdkOrderNote) use ($fulfilmentId) {
            return $pdkOrderNote->setApiIdentifier($fulfilmentId);
        })
        ->toArrayWithoutNull();

    expect($savedOrderNotes->toArrayWithoutNull())->toEqual($expected);
})->with([
    'single note' => [
        '657718',
        function () {
            return factory(PdkOrderNoteCollection::class)->push(
                factory(PdkOrderNote::class)
                    ->byWebshop()
                    ->withNote('This is a note')
                    ->withCreatedAt('2023-01-01 12:00:00')
                    ->withUpdatedAt('2023-01-01 12:00:00')
            );
        },
    ],

    'multiple notes' => [
        '12345678',
        function () {
            return factory(PdkOrderNoteCollection::class)->push(
                factory(PdkOrderNote::class)
                    ->byCustomer()
                    ->withNote('This is a note')
                    ->withCreatedAt('2023-01-01 12:00:00')
                    ->withUpdatedAt('2023-01-01 12:00:00'),

                factory(PdkOrderNote::class)
                    ->byWebshop()
                    ->withNote('This is another note')
                    ->withCreatedAt('2023-01-01 12:00:00')
                    ->withUpdatedAt('2023-01-01 12:00:00')
            );
        },
    ],
]);
