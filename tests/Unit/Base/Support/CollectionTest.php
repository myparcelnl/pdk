<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Pdk\Tests\Mocks\MockArrayable;
use MyParcelNL\Pdk\Tests\Mocks\MockCastingCollection;
use MyParcelNL\Pdk\Tests\Mocks\MockCastModel;
use MyParcelNL\Pdk\Tests\Mocks\MockStorableModel;

it('casts items to class on init', function () {
    $collection = new MockCastingCollection([
        new MockCastModel(['property' => 1]),
        ['property' => 2],
    ]);

    expect($collection->all())->toHaveLength(2);
    $collection->each(function ($item) {
        expect($item)->toBeInstanceOf(MockCastModel::class);
    });
});

it('casts items to class on push', function () {
    $collection = new MockCastingCollection();
    $collection->push(['property' => 6]);

    expect($collection->all())->toHaveLength(1);
    $collection->each(function ($item) {
        expect($item)->toBeInstanceOf(MockCastModel::class);
    });
});

it('merges another collection by key', function () {
    $collection = new Collection([
        ['id' => 1, 'name' => 'berend'],
        ['id' => 2, 'name' => 'piet'],
        ['id' => 3, 'name' => 'joep'],
    ]);

    $newCollection = new Collection([
        ['id' => 2, 'name' => 'willem'],
        ['id' => 3, 'name' => 'henk'],
        ['id' => 5, 'name' => 'klaas'],
    ]);

    $merged = $collection->mergeByKey($newCollection, 'id');

    $all = $merged->all();

    expect($all)->toEqual([
        ['id' => 1, 'name' => 'berend'],
        ['id' => 2, 'name' => 'willem'],
        ['id' => 3, 'name' => 'henk'],
        ['id' => 5, 'name' => 'klaas'],
    ]);
});

it('can create a storable array', function (array $items, array $storable) {
    $collection = new Collection($items);

    expect($collection->toStorableArray())->toEqual($storable);
})->with(function () {
    return [
        '1 storable model' => [
            'items'    => [
                new MockStorableModel(['property' => ['a' => 1]]),
            ],
            'storable' => [
                [
                    'property' => '{"a":1}',
                ],
            ],
        ],

        '1 non-storable model' => [
            'items'    => [
                new MockCastModel(['property' => 'test']),
            ],
            'storable' => [
                ['property' => 'test'],
            ],
        ],

        '2 storable models, 1 non-storable and a collection' => [
            'items'    => [
                new MockStorableModel(['property' => ['a' => 1]]),
                new MockStorableModel(['property' => ['b' => 2]]),
                'test',
                new Collection(['test' => 1, 'test2' => null, 'test3' => new MockArrayable(['ba' => null, 'bo' => 1])]
                ),
            ],
            'storable' => [
                ['property' => '{"a":1}'],
                ['property' => '{"b":2}'],
                'test',
                ['test' => 1, 'test3' => ['bo' => 1]],
            ],
        ],
    ];
});

it('can create an array without null', function () {
    $collection = new Collection([
        'a' => 1,
        'b' => null,
        'c' => ['d' => 2, 'e' => null],
        'd' => new Collection([
            'f' => 3,
            'g' => null,
            'z' => new MockArrayable(['a' => 1, 'b' => null]),
        ]),
        'e' => new MockCastModel(['property' => null]),
    ]);

    expect($collection->toArrayWithoutNull())->toEqual([
        'a' => 1,
        'c' => ['d' => 2],
        'd' => [
            'f' => 3,
            'z' => ['a' => 1],
        ],
        'e' => [],
    ]);
});
