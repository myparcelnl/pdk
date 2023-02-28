<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Tests\Mocks\MockCastingCollection;
use MyParcelNL\Pdk\Tests\Mocks\MockCastModel;

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
