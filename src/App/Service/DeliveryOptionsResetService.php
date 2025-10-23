<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Service;

use MyParcelNL\Pdk\Settings\Model\CarrierSettings;

/**
 * Service to handle resetting delivery options when delivery options are disabled.
 */
class DeliveryOptionsResetService
{
    /**
     * List of delivery option settings that should be reset when delivery options are disabled.
     */
    private const DELIVERY_OPTION_SETTINGS = [
        CarrierSettings::ALLOW_DELIVERY_OPTIONS,
        CarrierSettings::ALLOW_STANDARD_DELIVERY,
        CarrierSettings::ALLOW_MORNING_DELIVERY,
        CarrierSettings::ALLOW_EVENING_DELIVERY,
        CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
        CarrierSettings::ALLOW_MONDAY_DELIVERY,
        CarrierSettings::ALLOW_SATURDAY_DELIVERY,
        CarrierSettings::ALLOW_SIGNATURE,
        CarrierSettings::ALLOW_ONLY_RECIPIENT,
        CarrierSettings::ALLOW_PICKUP_LOCATIONS,
        CarrierSettings::ALLOW_DELIVERY_TYPE_EXPRESS,
    ];

    /**
     * Reset all delivery option settings to false on a carrier settings object.
     *
     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings $carrierSettings
     *
     * @return void
     */
    public function resetDeliveryOptions(CarrierSettings $carrierSettings): void
    {
        foreach (self::DELIVERY_OPTION_SETTINGS as $setting) {
            $carrierSettings->setAttribute($setting, false);
        }
    }

    /**
     * Get the list of delivery option settings that should be reset.
     *
     * @return array
     */
    public function getDeliveryOptionSettings(): array
    {
        return self::DELIVERY_OPTION_SETTINGS;
    }
}
