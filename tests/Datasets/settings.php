<?php

declare(strict_types=1);

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use function MyParcelNL\Pdk\Tests\factory;

dataset('carrierExportSettings', [
    'export age check' => fn() => factory(CarrierSettings::class)->withExportAgeCheck(true),

    'export large format' => fn() => factory(CarrierSettings::class)->withExportLargeFormat(true),

    'export only recipient' => fn() => factory(CarrierSettings::class)->withExportOnlyRecipient(true),

    'export return' => fn() => factory(CarrierSettings::class)->withExportReturn(true),

    'export signature' => fn() => factory(CarrierSettings::class)->withExportSignature(true),

    'export insurance' => fn() => factory(CarrierSettings::class)
        ->withExportInsurance(true)
        ->withExportInsuranceFromAmount(0),
]);

function getTriState2Data(bool $coerce): array
{
    return [
        '-1, -1 -> 0' => [-1, -1, $coerce ? 0 : -1],
        '-1,  1 -> 1' => [-1, 1, 1],
        '-1,  0 -> 0' => [-1, 0, 0],
        ' 0, -1 -> 1' => [0, -1, 1],
        ' 0, -1 -> 0' => [0, -1, 0],
        ' 0,  1 -> 1' => [0, 1, 1],
        ' 0,  0 -> 0' => [0, 0, 0],
        ' 1, -1 -> 1' => [1, -1, 1],
        ' 1,  1 -> 1' => [1, 1, 1],
        ' 1,  0 -> 0' => [1, 0, 0],
    ];
}

function getTriState3Data(bool $coerce): array
{
    return [
        '-1, -1, -1 -> 0' => [-1, -1, -1, $coerce ? 0 : -1],
        '-1, -1,  0 -> 0' => [-1, -1, 0, 0],
        '-1, -1,  1 -> 1' => [-1, -1, 1, 1],
        '-1,  0, -1 -> 0' => [-1, 0, -1, 0],
        '-1,  0,  0 -> 0' => [-1, 0, 0, 0],
        '-1,  0,  1 -> 1' => [-1, 0, 1, 1],
        '-1,  1, -1 -> 1' => [-1, 1, -1, 1],
        '-1,  1,  0 -> 0' => [-1, 1, 0, 0],
        '-1,  1,  1 -> 1' => [-1, 1, 1, 1],
        ' 0, -1, -1 -> 0' => [0, -1, -1, 0],
        ' 0, -1,  0 -> 0' => [0, -1, 0, 0],
        ' 0, -1,  1 -> 1' => [0, -1, 1, 1],
        ' 0,  0, -1 -> 0' => [0, 0, -1, 0],
        ' 0,  0,  0 -> 0' => [0, 0, 0, 0],
        ' 0,  0,  1 -> 1' => [0, 0, 1, 1],
        ' 0,  1, -1 -> 1' => [0, 1, -1, 1],
        ' 0,  1,  0 -> 0' => [0, 1, 0, 0],
        ' 0,  1,  1 -> 1' => [0, 1, 1, 1],
        ' 1, -1, -1 -> 1' => [1, -1, -1, 1],
        ' 1, -1,  0 -> 0' => [1, -1, 0, 0],
        ' 1, -1,  1 -> 1' => [1, -1, 1, 1],
        ' 1,  0, -1 -> 0' => [1, 0, -1, 0],
        ' 1,  0,  0 -> 0' => [1, 0, 0, 0],
        ' 1,  0,  1 -> 1' => [1, 0, 1, 1],
        ' 1,  1, -1 -> 1' => [1, 1, -1, 1],
        ' 1,  1,  0 -> 0' => [1, 1, 0, 0],
        ' 1,  1,  1 -> 1' => [1, 1, 1, 1],
    ];
}

dataset('triState2', getTriState2Data(false));
dataset('triState2Coerced', getTriState2Data(true));

dataset('triState3', getTriState3Data(false));
dataset('triState3Coerced', getTriState3Data(true));
dataset('triState3BoolFirst', [
    'false, -1, -1 -> 0' => [false, -1, -1, 0],
    'false, -1,  0 -> 0' => [false, -1, 0, 0],
    'false, -1,  1 -> 1' => [false, -1, 1, 1],
    'false,  0, -1 -> 0' => [false, 0, -1, 0],
    'false,  0,  0 -> 0' => [false, 0, 0, 0],
    'false,  0,  1 -> 1' => [false, 0, 1, 1],
    'false,  1, -1 -> 1' => [false, 1, -1, 1],
    'false,  1,  0 -> 0' => [false, 1, 0, 0],
    'false,  1,  1 -> 1' => [false, 1, 1, 1],
    ' true, -1, -1 -> 1' => [true, -1, -1, 1],
    ' true, -1,  0 -> 0' => [true, -1, 0, 0],
    ' true, -1,  1 -> 1' => [true, -1, 1, 1],
    ' true,  0, -1 -> 0' => [true, 0, -1, 0],
    ' true,  0,  0 -> 0' => [true, 0, 0, 0],
    ' true,  0,  1 -> 1' => [true, 0, 1, 1],
    ' true,  1, -1 -> 1' => [true, 1, -1, 1],
    ' true,  1,  0 -> 0' => [true, 1, 0, 0],
    ' true,  1,  1 -> 1' => [true, 1, 1, 1],
]);
