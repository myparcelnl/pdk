<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Definition\CountryOfOriginDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CustomsCodeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PackageTypeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

function createNestedProducts(
    OrderOptionDefinitionInterface $definition,
                                   $value1,
                                   $value2,
                                   $value3
): PdkProduct {
    $key = $definition->getProductSettingsKey();

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
    OrderOptionDefinitionInterface $definition,
    int                            $value1,
    int                            $value2,
    int                            $value3,
    int                            $result
) {
    $product = createNestedProducts($definition, $value1, $value2, $value3);
    $key     = $definition->getProductSettingsKey();

    expect($product->mergedSettings->getAttribute($key))->toEqual($result);
})
    ->with([
        'signature' => new SignatureDefinition(),
    ])
    ->with('triState3');

it('creates a storable array', function () {
    $product = factory(PdkProduct::class)->make();

    expect($product->toStorableArray())->toHaveKeys(['externalIdentifier', 'settings']);
});

it('calculates other options for child products', function (string $definitionClass, $input, $output) {
    /** @var OrderOptionDefinitionInterface $definition */
    $definition = new $definitionClass();

    $product = createNestedProducts($definition, ...$input);

    expect($product->mergedSettings->getAttribute($definition->getProductSettingsKey()))->toBe($output);
})->with([
    'country of origin: NL, BE, DE -> DE' => [
        'definition' => CountryOfOriginDefinition::class,
        'input'      => ['NL', 'BE', 'DE'],
        'output'     => 'DE',
    ],

    'country of origin: DE, -1, -1 -> DE' => [
        'definition' => CountryOfOriginDefinition::class,
        'input'      => ['DE', -1, -1],
        'output'     => 'DE',
    ],

    'country of origin: -1, FR, -1 -> FR' => [
        'definition' => CountryOfOriginDefinition::class,
        'input'      => [-1, 'FR', -1],
        'output'     => 'FR',
    ],

    'country of origin: -1, -1, NL -> NL' => [
        'definition' => CountryOfOriginDefinition::class,
        'input'      => [-1, -1, 'NL'],
        'output'     => 'NL',
    ],

    'customs code: a, b, _ -> b' => [
        'definition' => CustomsCodeDefinition::class,
        'input'      => ['a', 'b', -1],
        'output'     => 'b',
    ],

    'customs code: a, _, b -> b' => [
        'definition' => CustomsCodeDefinition::class,
        'input'      => ['a', -1, 'b'],
        'output'     => 'b',
    ],

    'customs code: _, a, b -> b' => [
        'definition' => CustomsCodeDefinition::class,
        'input'      => [-1, 'a', 'b'],
        'output'     => 'b',
    ],

    'package type: package, mailbox, _ -> mailbox' => [
        'definition' => PackageTypeDefinition::class,
        'input'      => ['package', 'mailbox', '-1'],
        'output'     => 'mailbox',
    ],

    'package type: package, _, mailbox -> mailbox' => [
        'definition' => PackageTypeDefinition::class,
        'input'      => ['package', '-1', 'mailbox'],
        'output'     => 'mailbox',
    ],

    'package type: _, package, mailbox -> mailbox' => [
        'definition' => PackageTypeDefinition::class,
        'input'      => ['-1', 'package', 'mailbox'],
        'output'     => 'mailbox',
    ],
]);
