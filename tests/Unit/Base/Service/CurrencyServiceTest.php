<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance([
        LanguageServiceInterface::class => autowire(MockAbstractLanguage::class),
    ])
);

it('calculates vat totals', function (array $input, array $expected) {
    /** @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface $currencyService */
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
    /** @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface $currencyService */
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

it('formats currencies', function (string $language, int $input, string $expected) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractLanguageService $languageService */
    $languageService = Pdk::get(LanguageServiceInterface::class);
    $languageService->setLanguage($language);

    /** @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface $currencyService */
    $currencyService = Pdk::get(CurrencyServiceInterface::class);
    expect($currencyService->format($input))->toEqual($expected);
})->with([
    'nl: 0'      => [
        'language' => 'nl',
        'input'    => 0,
        'output'   => '€ 0,00',
    ],
    'nl: 1'      => [
        'language' => 'nl',
        'input'    => 1,
        'output'   => '€ 0,01',
    ],
    'nl: 10'     => [
        'language' => 'nl',
        'input'    => 10,
        'output'   => '€ 0,10',
    ],
    'nl: 100'    => [
        'language' => 'nl',
        'input'    => 100,
        'output'   => '€ 1,00',
    ],
    'nl: 1000'   => [
        'language' => 'nl',
        'input'    => 1000,
        'output'   => '€ 10,00',
    ],
    'nl: 10000'  => [
        'language' => 'nl',
        'input'    => 10000,
        'output'   => '€ 100,00',
    ],
    'nl: 100000' => [
        'language' => 'nl',
        'input'    => 100000,
        'output'   => '€ 1.000,00',
    ],
    'en: 0'      => [
        'language' => 'en',
        'input'    => 0,
        'output'   => '€0.00',
    ],
    'en: 1'      => [
        'language' => 'en',
        'input'    => 1,
        'output'   => '€0.01',
    ],
    'en: 10'     => [
        'language' => 'en',
        'input'    => 10,
        'output'   => '€0.10',
    ],
    'en: 100'    => [
        'language' => 'en',
        'input'    => 100,
        'output'   => '€1.00',
    ],
    'en: 1000'   => [
        'language' => 'en',
        'input'    => 1000,
        'output'   => '€10.00',
    ],
    'en: 10000'  => [
        'language' => 'en',
        'input'    => 10000,
        'output'   => '€100.00',
    ],
    'en: 100000' => [
        'language' => 'en',
        'input'    => 100000,
        'output'   => '€1,000.00',
    ],
]);
