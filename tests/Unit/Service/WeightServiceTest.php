<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Service\WeightService;
use MyParcelNL\Sdk\src\Exception\ValidationException;

const CUSTOM_RANGES = [
    [
        'min'     => 0,
        'max'     => 600,
        'average' => 300,
    ],
    [
        'min'     => 600,
        'max'     => 6000,
        'average' => 3300,
    ],
];

it('converts units correctly', function ($unit, $input, $expectation) {
    expect(WeightService::convertToGrams($input, $unit))->toEqual($expectation);
})->with([
    [WeightService::UNIT_GRAMS, 50, 50],
    [WeightService::UNIT_KILOGRAMS, 50, 50000],
    [WeightService::UNIT_POUNDS, 50, 22680],
    [WeightService::UNIT_OUNCES, 50, 1418],
]);

it('throws error with unknown unit', function () {
    WeightService::convertToGrams(0.1, 'bloemkool');
})->throws(InvalidArgumentException::class);

it('converts to digital stamp correctly', function ($input, $expectation) {
    expect(WeightService::convertToDigitalStamp($input))->toEqual($expectation);
})->with([
    [-1, 15],
    [0, 15],
    [20, 15],
    [21, 35],
    [49, 35],
    [50, 35],
    [51, 75],
    [100, 75],
    [350, 225],
    [351, 1175],
    [1000, 1175],
]);

it('throws error when weight is higher than max for digital stamp', function ($input, $errorClassName) {
    $this->expectException($errorClassName);
    WeightService::convertToDigitalStamp($input);
})->with([
    [3000, ValidationException::class],
]);

it('converts to digital stamp using custom range correctly', function ($ranges, $input, $expectation) {
    expect(WeightService::convertToDigitalStamp($input, $ranges))->toEqual($expectation);
})->with([
    [CUSTOM_RANGES, CUSTOM_RANGES[0]['min'], CUSTOM_RANGES[0]['average']],
    [CUSTOM_RANGES, CUSTOM_RANGES[0]['max'], CUSTOM_RANGES[0]['average']],
    [CUSTOM_RANGES, CUSTOM_RANGES[0]['max'] + 1, CUSTOM_RANGES[1]['average']],
    [CUSTOM_RANGES, CUSTOM_RANGES[1]['min'], CUSTOM_RANGES[0]['average']],
    [CUSTOM_RANGES, CUSTOM_RANGES[1]['max'], CUSTOM_RANGES[1]['average']],
]);
