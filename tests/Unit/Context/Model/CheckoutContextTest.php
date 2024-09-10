<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractLanguageService;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\get;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance([
    LanguageServiceInterface::class => get(MockAbstractLanguageService::class),
]));

beforeEach(function () {
    /** @var MockAbstractLanguageService $languageService */
    $languageService = Pdk::get(LanguageServiceInterface::class);

    $languageService->setTranslations('en', [
        'apple_tree'                     => 'Appelboom',
        'delivery_options'               => 'Delivery options',
        'delivery_options_morning'       => 'Ochtend',
        'delivery_options_signature'     => 'Handtekening',
        'some_delivery_options_broccoli' => 'Broccoli',
        'some_delivery_options_carrot'   => 'Carrot',
    ]);
});

it('gets strings', function () {
    $context = new CheckoutContext();

    expect($context->strings)->toEqual([
        'morning'               => 'Ochtend',
        'signature'             => 'Handtekening',
        'headerDeliveryOptions' => null,
    ]);
});

it('gets strings with custom header', function () {
    factory(CheckoutSettings::class)
        ->withDeliveryOptionsHeader('Joepie')
        ->store();

    $context = new CheckoutContext();

    expect($context->strings)->toEqual([
        'morning'               => 'Ochtend',
        'signature'             => 'Handtekening',
        'headerDeliveryOptions' => 'Joepie',
    ]);
});

it('can add custom properties to settings', function () {
    factory(CheckoutSettings::class)->store();

    $context           = new CheckoutContext();
    $context->settings = array_merge($context->settings, [
        'test' => 1,
    ]);

    $array = $context->toArray();

    expect($array['settings'])->toHaveKeysAndValues([
        'test' => 1,
    ]);
});
