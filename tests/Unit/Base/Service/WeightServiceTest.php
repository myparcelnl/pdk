<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\PackageType;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

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

usesShared(new UsesMockPdkInstance());

it('converts to grams', function (string $unit, $input, int $expectation) {
    /** @var \MyParcelNL\Pdk\Base\Contract\WeightServiceInterface $weightService */
    $weightService = Pdk::get(WeightServiceInterface::class);

    expect($weightService->convertToGrams($input, $unit))->toEqual($expectation);
})->with([
    [WeightServiceInterface::UNIT_GRAMS, 50, 50],
    [WeightServiceInterface::UNIT_KILOGRAMS, 50, 50000],
    [WeightServiceInterface::UNIT_POUNDS, 50, 22680],
    [WeightServiceInterface::UNIT_OUNCES, 0.5, 15],
]);

it('throws error with unknown unit', function () {
    /** @var \MyParcelNL\Pdk\Base\Contract\WeightServiceInterface $weightService */
    $weightService = Pdk::get(WeightServiceInterface::class);

    $weightService->convertToGrams(0.1, 'bloemkool');
})->throws(InvalidArgumentException::class);

it('converts weight to digital stamp range', function ($input, $expectation) {
    /** @var \MyParcelNL\Pdk\Base\Contract\WeightServiceInterface $weightService */
    $weightService = Pdk::get(WeightServiceInterface::class);

    expect($weightService->convertToDigitalStamp($input))->toEqual($expectation);
})->with([
    [-1, 15],
    [0, 15],
    [20, 15],
    [21, 35],
    [49, 35],
    [50, 35],
    [51, 200],
    [100, 200],
    [350, 200],
    [351, 1175],
    [1000, 1175],
]);

it('throws error when weight is higher than max for digital stamp', function ($input, $errorClassName) {
    /** @var \MyParcelNL\Pdk\Base\Contract\WeightServiceInterface $weightService */
    $weightService = Pdk::get(WeightServiceInterface::class);

    expect(function () use ($weightService, $input) {
        $weightService->convertToDigitalStamp($input);
    })->toThrow($errorClassName);
})->with([
    [3000, InvalidArgumentException::class],
]);

it('converts to digital stamp using custom range', function ($ranges, $input, $expectation) {
    /** @var \MyParcelNL\Pdk\Base\Contract\WeightServiceInterface $weightService */
    $weightService = Pdk::get(WeightServiceInterface::class);

    expect($weightService->convertToDigitalStamp($input, $ranges))->toEqual($expectation);
})->with([
    [CUSTOM_RANGES, CUSTOM_RANGES[0]['min'], CUSTOM_RANGES[0]['average']],
    [CUSTOM_RANGES, CUSTOM_RANGES[0]['max'], CUSTOM_RANGES[0]['average']],
    [CUSTOM_RANGES, CUSTOM_RANGES[0]['max'] + 1, CUSTOM_RANGES[1]['average']],
    [CUSTOM_RANGES, CUSTOM_RANGES[1]['min'], CUSTOM_RANGES[0]['average']],
    [CUSTOM_RANGES, CUSTOM_RANGES[1]['max'], CUSTOM_RANGES[1]['average']],
]);

it('adds empty package weight', function ($weight, $packageTypeName, $expectation) {
    factory(OrderSettings::class)
        ->withEmptyParcelWeight(200)
        ->withEmptyMailboxWeight(100)
        ->withEmptyDigitalStampWeight(50)
        ->withEmptyPackageSmallWeight(75)
        ->store();

    $packageType = factory(PackageType::class)->withName($packageTypeName)->make();

    /** @var \MyParcelNL\Pdk\Base\Contract\WeightServiceInterface $weightService */
    $weightService = Pdk::get(WeightServiceInterface::class);

    expect($weightService->addEmptyPackageWeight($weight, $packageType))->toEqual($expectation);
})->with([
    [1000, 'package', 1200],
    [1000, 'mailbox', 1100],
    [1000, 'digital_stamp', 1050],
    [1000, 'package_small', 1075],
]);
