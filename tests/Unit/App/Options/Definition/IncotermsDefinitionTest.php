<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ShipmentDefsIncotermsRule;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('maps incoterms to the top-level incoterms capability with only a carrier setting', function () {
    $definition = new IncotermsDefinition();

    expect($definition->getShipmentOptionsKey())->toBe('incoterms')
        ->and($definition->getCapabilitiesOptionsKey())->toBeNull()
        ->and($definition->getCapabilitiesRootKey())->toBe('incoterms')
        ->and($definition->getCarrierSettingsKey())->toBe('exportIncoterms')
        ->and($definition->getProductSettingsKey())->toBeNull()
        ->and($definition->getAllowSettingsKey())->toBeNull()
        ->and($definition->getPriceSettingsKey())->toBeNull();
});

it('keeps a rule and INHERIT on the shipment options', function ($value) {
    $shipmentOptions = new ShipmentOptions(['incoterms' => $value]);

    expect($shipmentOptions->incoterms)->toBe($value);
})->with([
    'rule'    => [ShipmentDefsIncotermsRule::DDP],
    'inherit' => [TriStateService::INHERIT],
]);

it('maps the rule to and from the capabilities keys', function () {
    $shipmentOptions = new ShipmentOptions(['incoterms' => ShipmentDefsIncotermsRule::DDP]);

    $capabilities = ShipmentOptions::toCapabilitiesDefinitions($shipmentOptions);

    expect($capabilities['incoterms'])->toBe(ShipmentDefsIncotermsRule::DDP)
        ->and(ShipmentOptions::fromCapabilitiesDefinitions($capabilities)->incoterms)
        ->toBe(ShipmentDefsIncotermsRule::DDP);
});
