<?php

namespace MyParcelNL\Pdk\Settings\Adapter;

use MyParcelNL\Pdk\Settings\Collection\CarrierSettingsCollection;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;

class DeliveryOptionsConfigAdapter
{
    private $adapted;

    /**
     * @param  mixed $settingsCollection
     */
    public function __construct(CarrierSettingsCollection $settingsCollection)
    {
        $this->adapted = [];

        $this->adapt($settingsCollection);
    }

    private function adapt(CarrierSettingsCollection $settingsCollection): void
    {
        foreach ($settingsCollection as $carrierSettings) {
            if (! $carrierSettings[CarrierSettings::CARRIER_NAME]) {
                continue;
            }

            $this->adapted[$carrierSettings[CarrierSettings::CARRIER_NAME]] = $this->adaptSettings(
                $carrierSettings->toArray()
            );
        }
    }

    private function adaptSettings(array $settings): array
    {
        $settings['allowShowDeliveryDate'] = $settings['featureShowDeliveryDate'];
        $dropOffPossibilities              = $settings[CarrierSettings::DROP_OFF_POSSIBILITIES];

        unset(
            $settings['featureShowDeliveryDate'],
            $settings[CarrierSettings::CARRIER_NAME],
            $settings[CarrierSettings::DROP_OFF_POSSIBILITIES]
        );

        if (! $dropOffPossibilities) {
            return $settings;
        }

        $settings['dropOffDays'] = [];
        foreach ($dropOffPossibilities['dropOffDays'] as $dropOffDay) {
            if ($dropOffDay['dispatch']) {
                $weekDay                   = $dropOffDay['weekday'];
                $settings['dropOffDays'][] = $weekDay;
                if (! isset($settings['cutoffTime'])) {
                    $settings['cutoffTime'] = $dropOffDay['cutoffTime'];
                }
                if (! isset($settings['cutoffTimeSameDay'])) {
                    $settings['cutoffTimeSameDay'] = $dropOffDay['sameDayCutoffTime'] ?? null;
                }
                if (DropOffDay::WEEKDAY_SATURDAY === $weekDay) {
                    $settings['saturdayCutoffTime'] = $dropOffDay['cutoffTime'];
                }
            }
        }

        $settings['deliveryDaysWindow'] = $dropOffPossibilities['deliveryDaysWindow'];
        $settings['dropOffDelay']       = $dropOffPossibilities['dropOffDelay'];

        return $settings;
    }

    public function toArray(): array
    {
        return $this->adapted;
    }
}
