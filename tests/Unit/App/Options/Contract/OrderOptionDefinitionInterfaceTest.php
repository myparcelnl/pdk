<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Contract;

use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CountryOfOriginDefinition;
use MyParcelNL\Pdk\App\Options\Definition\CustomsCodeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DisableDeliveryOptionsDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FitInDigitalStampDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FitInMailboxDefinition;
use MyParcelNL\Pdk\App\Options\Definition\HideSenderDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PackageTypeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SameDayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesEachMockPdkInstance());

$definitions = [
    AgeCheckDefinition::class,
    CountryOfOriginDefinition::class,
    CustomsCodeDefinition::class,
    DirectReturnDefinition::class,
    DisableDeliveryOptionsDefinition::class,
    FitInDigitalStampDefinition::class,
    FitInMailboxDefinition::class,
    HideSenderDefinition::class,
    InsuranceDefinition::class,
    LargeFormatDefinition::class,
    OnlyRecipientDefinition::class,
    PackageTypeDefinition::class,
    SameDayDeliveryDefinition::class,
    SignatureDefinition::class,
];

it('snapshots all definitions', function () use ($definitions) {
    $items = array_map(function (string $definition) {
        /** @var \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $instance */
        $instance = new $definition();

        return [
            'class'              => $definition,
            'carrierSettingsKey' => $instance->getCarrierSettingsKey(),
            'productSettingsKey' => $instance->getProductSettingsKey(),
            'shipmentOptionsKey' => $instance->getShipmentOptionsKey(),
        ];
    }, $definitions);

    assertMatchesJsonSnapshot(json_encode((new Collection($items))->toArrayWithoutNull()));
});

it('can validate', function () use ($definitions) {
    $fakeCarrier = factory(Carrier::class)
        ->withOutboundFeatures(factory(PropositionCarrierFeatures::class)->withEverything())
        ->make();

    $carrierSchema = Pdk::get(CarrierSchema::class);
    $carrierSchema->setCarrier($fakeCarrier);

    $array = array_map(function ($definition) use ($carrierSchema) {
        /** @var \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $instance */
        $instance = new $definition();

        return $instance->validate($carrierSchema);
    }, $definitions);

    expect($array)->each->toBeTrue();
});
