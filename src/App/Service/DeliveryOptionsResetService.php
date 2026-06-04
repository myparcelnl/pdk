<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Service;

use MyParcelNL\Pdk\App\DeliveryOptions\Service\DeliveryOptionsService;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;

/**
 * Service to handle resetting delivery options when delivery options are disabled.
 */
class DeliveryOptionsResetService
{
    /**
     * Reset all delivery option settings to false on a carrier settings object.
     *
     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings $carrierSettings
     *
     * @return void
     */
    public function resetDeliveryOptions(CarrierSettings $carrierSettings): void
    {
        foreach ($this->getDeliveryOptionSettings() as $setting) {
            $carrierSettings->setAttribute($setting, false);
        }
    }

    /**
     * The carrier-settings attribute names this service resets.
     *
     * Derived from the canonical map exposed by {@see DeliveryOptionsService::getCarrierSettingsMap()}
     * — keeping the source of truth in one place. The allow-* subset is selected because
     * prices follow from the allow state; resetting prices is unnecessary.
     *
     * @return string[]
     */
    public function getDeliveryOptionSettings(): array
    {
        return array_values(array_filter(
            DeliveryOptionsService::getCarrierSettingsMap(),
            static function (string $attribute): bool {
                return strncmp($attribute, 'allow', 5) === 0;
            }
        ));
    }
}
