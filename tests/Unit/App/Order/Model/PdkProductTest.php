<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

function createNestedProducts(
    string $key,
           $value1,
           $value2,
           $value3
): PdkProduct {
    $product = factory(PdkProduct::class)
        ->withExternalIdentifier('shirt')
        ->withSettings(factory(ProductSettings::class)->with([$key => $value1]))
        ->store();

    $productLevel2 = factory(PdkProduct::class)
        ->withExternalIdentifier('shirt--crew')
        ->withParent($product)
        ->withSettings(factory(ProductSettings::class)->with([$key => $value2]))
        ->store();

    $productLevel3 = factory(PdkProduct::class)
        ->withExternalIdentifier('shirt--crew--red')
        ->withParent($productLevel2)
        ->withSettings(factory(ProductSettings::class)->with([$key => $value3]))
        ->store();

    return $productLevel3->make();
}

it('merges parent settings correctly', function (
    string $key,
    int    $value1,
    int    $value2,
    int    $value3,
    int    $result
) {
    $product = createNestedProducts($key, $value1, $value2, $value3);

    expect($product->mergedSettings->getAttribute($key))->toEqual($result);
})
    ->with([
        'signature' => 'exportSignature',
    ])
    ->with('triState3');

it('creates a storable array', function () {
    $product = factory(PdkProduct::class)->make();

    expect($product->toStorableArray())->toHaveKeys(['externalIdentifier', 'settings']);
});

it('calculates other options for child products', function (string $key, $input, $output) {
    $product = createNestedProducts($key, ...$input);

    expect($product->mergedSettings->getAttribute($key))->toBe($output);
})->with([
    'country of origin: NL, BE, DE -> DE' => [
        'key'    => 'countryOfOrigin',
        'input'  => ['NL', 'BE', 'DE'],
        'output' => 'DE',
    ],

    'country of origin: DE, -1, -1 -> DE' => [
        'key'    => 'countryOfOrigin',
        'input'  => ['DE', -1, -1],
        'output' => 'DE',
    ],

    'country of origin: -1, FR, -1 -> FR' => [
        'key'    => 'countryOfOrigin',
        'input'  => [-1, 'FR', -1],
        'output' => 'FR',
    ],

    'country of origin: -1, -1, NL -> NL' => [
        'key'    => 'countryOfOrigin',
        'input'  => [-1, -1, 'NL'],
        'output' => 'NL',
    ],

    'customs code: a, b, _ -> b' => [
        'key'    => 'customsCode',
        'input'  => ['a', 'b', -1],
        'output' => 'b',
    ],

    'customs code: a, _, b -> b' => [
        'key'    => 'customsCode',
        'input'  => ['a', -1, 'b'],
        'output' => 'b',
    ],

    'customs code: _, a, b -> b' => [
        'key'    => 'customsCode',
        'input'  => [-1, 'a', 'b'],
        'output' => 'b',
    ],

    'package type: package, mailbox, _ -> mailbox' => [
        'key'    => 'packageType',
        'input'  => ['package', 'mailbox', '-1'],
        'output' => 'mailbox',
    ],

    'package type: package, _, mailbox -> mailbox' => [
        'key'    => 'packageType',
        'input'  => ['package', '-1', 'mailbox'],
        'output' => 'mailbox',
    ],

    'package type: _, package, mailbox -> mailbox' => [
        'key'    => 'packageType',
        'input'  => ['-1', 'package', 'mailbox'],
        'output' => 'mailbox',
    ],
]);
