<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderNoteRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

const EXAMPLE_NOTE = [
    'orderIdentifier' => '5432',
    'apiIdentifier'   => null,
    'author'          => 'pdk',
    'note'            => 'test',
];

const DEFAULT_NOTES = [
    '123' => [
        [
            'orderIdentifier' => '123',
            'apiIdentifier'   => null,
            'author'          => 'customer',
            'note'            => 'customer 1',
        ],
        [
            'orderIdentifier' => '123',
            'apiIdentifier'   => null,
            'author'          => 'webshop',
            'note'            => 'webshop 1',
        ],
        [
            'orderIdentifier' => '123',
            'apiIdentifier'   => null,
            'author'          => 'webshop',
            'note'            => 'webshop 2',
        ],
    ],
    '456' => [
        [
            'orderIdentifier' => '456',
            'apiIdentifier'   => null,
            'author'          => 'webshop',
            'note'            => 'webshop 456',
        ],
    ],
];

usesShared(
    new UsesMockPdkInstance([
        PdkOrderNoteRepositoryInterface::class => autowire(MockPdkOrderNoteRepository::class)->constructor(
            DEFAULT_NOTES
        ),
    ])
);

it('adds note to order', function () {
    $note = new PdkOrderNote(EXAMPLE_NOTE);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderNoteRepository $repository */
    $repository = Pdk::get(PdkOrderNoteRepositoryInterface::class);

    $pdkOrder = new PdkOrder(['externalIdentifier' => '5432']);

    $repository->add($note);
    $addedNote = $repository->getFromOrder($pdkOrder);

    expect($addedNote)
        ->toBeInstanceOf(PdkOrderNoteCollection::class)
        ->and(
            $addedNote->first()
                ->toArray()
        )
        ->toEqual([
            'externalIdentifier' => null,
            'orderIdentifier'    => '5432',
            'apiIdentifier'      => null,
            'author'             => 'pdk',
            'note'               => 'test',
            'createdAt'          => null,
            'updatedAt'          => null,
        ]);
});

it('gets notes for order', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderNoteRepository $repository */
    $repository = Pdk::get(PdkOrderNoteRepositoryInterface::class);

    $pdkOrder = new PdkOrder(['externalIdentifier' => '123']);

    $notes = $repository->getFromOrder($pdkOrder);

    expect($notes)
        ->toBeInstanceOf(PdkOrderNoteCollection::class)
        ->and(Arr::dot($notes->toStorableArray()))
        ->toEqual([
            '0.orderIdentifier' => '123',
            '0.author'          => 'customer',
            '0.note'            => 'customer 1',
            '1.orderIdentifier' => '123',
            '1.author'          => 'webshop',
            '1.note'            => 'webshop 1',
            '2.orderIdentifier' => '123',
            '2.author'          => 'webshop',
            '2.note'            => 'webshop 2',
        ]);
});
