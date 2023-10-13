<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('calculates total weight', function (array $input, int $result) {
    $physicalProperties = factory(PhysicalProperties::class)
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
    $physicalProperties = factory(PhysicalProperties::class)
        ->withInitialWeight(2000)
        ->withWidth(20)
        ->withHeight(30)
        ->withLength(40)
        ->withManualWeight($manualWeight)
        ->make();

    expect($physicalProperties->toStorableArray())->toBe($result);
})->with([
    'manual weight set' => [2000, ['manualWeight' => 2000]],
    'manual weight -1'  => [TriStateService::INHERIT, []],
]);
