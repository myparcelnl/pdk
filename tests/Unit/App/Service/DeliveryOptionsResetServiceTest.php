<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Service;

use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
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

    expect($settings)->toHaveLength(12)
        ->and($settings)->toContain(
            CarrierSettings::ALLOW_DELIVERY_OPTIONS,
            CarrierSettings::ALLOW_STANDARD_DELIVERY,
            CarrierSettings::ALLOW_MORNING_DELIVERY,
            CarrierSettings::ALLOW_EVENING_DELIVERY,
            CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
            CarrierSettings::ALLOW_MONDAY_DELIVERY,
            CarrierSettings::ALLOW_SATURDAY_DELIVERY,
            CarrierSettings::ALLOW_SIGNATURE,
            CarrierSettings::ALLOW_ONLY_RECIPIENT,
            CarrierSettings::ALLOW_PRIORITY_DELIVERY,
            CarrierSettings::ALLOW_PICKUP_LOCATIONS,
            CarrierSettings::ALLOW_DELIVERY_TYPE_EXPRESS
        );
});
