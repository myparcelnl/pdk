<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Helper;

use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('settings', 'tri-state');

usesShared(new UsesMockPdkInstance());

it('gets value from product settings', function (string $definitionClass, $result) {
    $order = factory(PdkOrder::class)->make();

    $helper = new ProductSettingsDefinitionHelper($order);

    expect($helper->get(new $definitionClass()))->toEqual($result);
})->with([
    'age check'      => [AgeCheckDefinition::class, TriStateService::INHERIT],
    'direct return'  => [DirectReturnDefinition::class, TriStateService::INHERIT],
    'large format'   => [LargeFormatDefinition::class, TriStateService::INHERIT],
    'only recipient' => [OnlyRecipientDefinition::class, TriStateService::INHERIT],
    'signature'      => [SignatureDefinition::class, TriStateService::INHERIT],
]);

it('gets value from product settings with all options enabled', function (string $definitionClass, $result) {
    $order = factory(PdkOrder::class)
        ->withLines(
            factory(PdkOrderLineCollection::class)->push(factory(PdkOrderLine::class)->withProductWithAllSettings())
        )
        ->make();

    $helper = new ProductSettingsDefinitionHelper($order);

    expect($helper->get(new $definitionClass()))->toEqual($result);
})->with([
    'age check'      => [AgeCheckDefinition::class, TriStateService::ENABLED],
    'direct return'  => [DirectReturnDefinition::class, TriStateService::ENABLED],
    'large format'   => [LargeFormatDefinition::class, TriStateService::ENABLED],
    'only recipient' => [OnlyRecipientDefinition::class, TriStateService::ENABLED],
    'signature'      => [SignatureDefinition::class, TriStateService::ENABLED],
]);

