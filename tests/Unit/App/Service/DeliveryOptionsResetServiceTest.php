<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Service;

use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PriorityDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SameDayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SaturdayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\Base\Support\SettingKey;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('resets all delivery options to false', function () {
    $service = new DeliveryOptionsResetService();
    $carrierSettings = factory(CarrierSettings::class)->make([
        'allowDeliveryOptions' => true,
        'allowStandardDelivery' => true,
        'allowMorningDelivery' => true,
        'allowEveningDelivery' => true,
        'allowSameDayDelivery' => true,
        'allowMondayDelivery' => true,
        'allowSaturdayDelivery' => true,
        'allowSignature' => true,
        'allowOnlyRecipient' => true,
        'allowPriorityDelivery' => true,
        'allowPickupLocations' => true,
        'allowDeliveryTypeExpress' => true,
    ]);

    $service->resetDeliveryOptions($carrierSettings);

    expect($carrierSettings->allowDeliveryOptions)->toBeFalse()
        ->and($carrierSettings->allowStandardDelivery)->toBeFalse()
        ->and($carrierSettings->allowMorningDelivery)->toBeFalse()
        ->and($carrierSettings->allowEveningDelivery)->toBeFalse()
        ->and($carrierSettings->allowSameDayDelivery)->toBeFalse()
        ->and($carrierSettings->allowMondayDelivery)->toBeFalse()
        ->and($carrierSettings->allowSaturdayDelivery)->toBeFalse()
        ->and($carrierSettings->allowSignature)->toBeFalse()
        ->and($carrierSettings->allowOnlyRecipient)->toBeFalse()
        ->and($carrierSettings->allowPriorityDelivery)->toBeFalse()
        ->and($carrierSettings->allowPickupLocations)->toBeFalse()
        ->and($carrierSettings->allowDeliveryTypeExpress)->toBeFalse();
});

it('returns the correct list of delivery option settings', function () {
    $service = new DeliveryOptionsResetService();
    $settings = $service->getDeliveryOptionSettings();

    // Length not pinned — auto-derivation may grow the list as the SDK enum
    // (or DELIVERY_OPTION_*) gains entries. What matters is that the known
    // delivery toggles are all present.
    expect($settings)->toContain(
        SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_ALLOW_HOME),
        SettingKey::allow(RefTypesDeliveryTypeV2::STANDARD),
        SettingKey::allow(RefTypesDeliveryTypeV2::MORNING),
        SettingKey::allow(RefTypesDeliveryTypeV2::EVENING),
        (new SameDayDeliveryDefinition())->getAllowSettingsKey(),
        SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_MONDAY),
        (new SaturdayDeliveryDefinition())->getAllowSettingsKey(),
        (new SignatureDefinition())->getAllowSettingsKey(),
        (new OnlyRecipientDefinition())->getAllowSettingsKey(),
        (new PriorityDeliveryDefinition())->getAllowSettingsKey(),
        SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP),
        // Storage attribute is the legacy 'allowDeliveryTypeExpress', not the clean expressDelivery key.
        'allowDeliveryTypeExpress'
    );
});
