<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('calculates total weight', function (array $input, int $result) {
    $physicalProperties = factory(PdkPhysicalProperties::class)
        ->fromScratch()
        ->with($input)
        ->make();

    expect($physicalProperties->totalWeight)->toBe($result);
})->with([
    'only initialWeight' => [
        'input'  => [
            'initialWeight' => 1000,
        ],
        'result' => 1000,
    ],

    'initialWeight and manualWeight' => [
        'input'  => [
            'initialWeight' => 1000,
            'manualWeight'  => 2000,
        ],
        'result' => 2000,
    ],

    'initialWeight and manualWeight set to -1' => [
        'input'  => [
            'initialWeight' => 1000,
            'manualWeight'  => TriStateService::INHERIT,
        ],
        'result' => 1000,
    ],
]);

it('creates a storable array', function (int $manualWeight, array $result) {
    $physicalProperties = factory(PdkPhysicalProperties::class)
        ->withInitialWeight(2000)
        ->withWidth(20)
        ->withHeight(30)
        ->withLength(40)
        ->withManualWeight($manualWeight)
        ->make();

    expect($physicalProperties->toStorableArray())->toBe($result);
})->with([
    'manual weight set' => [
        2000,
        ['height' => 30, 'length' => 40, 'width' => 20, 'manualWeight' => 2000],
    ],
    'manual weight -1'  => [
        TriStateService::INHERIT,
        ['height' => 30, 'length' => 40, 'width' => 20],
    ],
]);

it('only stores dimensions the merchant filled in', function ($input, array $result) {
    $physicalProperties = factory(PdkPhysicalProperties::class)
        ->fromScratch()
        ->make();

    $physicalProperties->fill(['height' => $input, 'length' => $input, 'width' => $input]);

    expect($physicalProperties->toStorableArray())->toBe($result);
})->with([
    'empty string is omitted, not stored as 0' => [
        '',
        [],
    ],

    'null is omitted' => [
        null,
        [],
    ],

    'non-numeric input is omitted' => [
        'abc',
        [],
    ],

    'zero is stored as-is' => [
        0,
        ['height' => 0, 'length' => 0, 'width' => 0],
    ],

    'negative values are stored as-is' => [
        -1,
        ['height' => -1, 'length' => -1, 'width' => -1],
    ],

    'numeric strings are cast to int' => [
        '30',
        ['height' => 30, 'length' => 30, 'width' => 30],
    ],
]);

it('clears a previously filled dimension when the field is emptied', function () {
    $physicalProperties = factory(PdkPhysicalProperties::class)
        ->fromScratch()
        ->with(['height' => 30, 'length' => 40, 'width' => 20])
        ->make();

    $physicalProperties->fill(['height' => '']);

    expect($physicalProperties->height)
        ->toBeNull()
        ->and($physicalProperties->toStorableArray())
        ->toBe(['length' => 40, 'width' => 20]);
});
