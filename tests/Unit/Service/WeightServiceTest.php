<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Service\WeightService;

it("converts units correctly", function ($unit, $input, $expectation) {
    expect(WeightService::convertToGrams($input, $unit))->toEqual($expectation);
})->with([
    [WeightService::UNIT_GRAMS, 50, 50],
    [WeightService::UNIT_KILOGRAMS, 50, 50000],
    [WeightService::UNIT_POUNDS, 50, 22680],
    [WeightService::UNIT_OUNCES, 50, 1418],
]);

