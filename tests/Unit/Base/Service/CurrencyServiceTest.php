<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Concern\CurrencyServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('calculates vat totals', function (array $input, array $expected) {
    /** @var \MyParcelNL\Pdk\Base\Concern\CurrencyServiceInterface $currencyService */
    $currencyService = Pdk::get(CurrencyServiceInterface::class);
    expect($currencyService->calculateVatTotals($input))->toEqual($expected);
})->with([
    'all'                     => [
        'input'    => [
            'price'         => 250,
            'vat'           => 10,
            'priceAfterVat' => 260,
        ],
        'expected' => [
            'price'         => 250,
            'vat'           => 10,
            'priceAfterVat' => 260,
        ],
    ],
    'price and vat'           => [
        'input'  => [
            'price' => 100,
            'vat'   => 48,
        ],
        'output' => [
            'price'         => 100,
            'vat'           => 48,
            'priceAfterVat' => 148,
        ],
    ],
    'price and priceAfterVat' => [
        'input'  => [
            'price'         => 100,
            'priceAfterVat' => 148,
        ],
        'output' => [
            'price'         => 100,
            'vat'           => 48,
            'priceAfterVat' => 148,
        ],
    ],
    'priceAfterVat and vat'   => [
        'input'  => [
            'priceAfterVat' => 148,
            'vat'           => 48,
        ],
        'output' => [
            'price'         => 100,
            'vat'           => 48,
            'priceAfterVat' => 148,
        ],
    ],
    'price and 0 vat'         => [
        'input'  => [
            'price' => 100,
            'vat'   => 0,
        ],
        'output' => [
            'price'         => 100,
            'vat'           => 0,
            'priceAfterVat' => 100,
        ],
    ],
    'only price'              => [
        'input'  => [
            'price' => 100,
        ],
        'output' => [
            'price'         => 100,
            'vat'           => 0,
            'priceAfterVat' => 100,
        ],
    ],
    'only vat'                => [
        'input'  => [
            'vat' => 48,
        ],
        'output' => [
            'price'         => 0,
            'vat'           => 48,
            'priceAfterVat' => 48,
        ],
    ],
    'only priceAfterVat'      => [
        'input'  => [
            'priceAfterVat' => 148,
        ],
        'output' => [
            'price'         => 148,
            'vat'           => 0,
            'priceAfterVat' => 148,
        ],
    ],
]);

it('converts to cents', function ($input, int $expected) {
    /** @var \MyParcelNL\Pdk\Base\Concern\CurrencyServiceInterface $currencyService */
    $currencyService = Pdk::get(CurrencyServiceInterface::class);
    expect($currencyService->convertToCents($input))->toEqual($expected);
})->with([
    'float 1.00'   => [
        'input'  => 1.00,
        'output' => 100,
    ],
    'float 46.22'  => [
        'input'  => 46.22,
        'output' => 4622,
    ],
    'int 1028'     => [
        'input'  => 1028,
        'output' => 102800,
    ],
    'string 14.00' => [
        'input'  => '14.00',
        'output' => 1400,
    ],
]);

