<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions as DeliveryOptionsModel;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;

/**
 * @property string $apiBaseUrl
 * @property string $currency
 * @property string $packageType
 * @property string $locale
 * @property string $platform
 * @property int    $basePrice
 * @property bool   $showPriceSurcharge
 * @property string $pickupLocationsDefaultView
 * @property int    $priceStandardDelivery
 * @property array  $carrierSettings
 */
class DeliveryOptions extends Model
{
    private const CARRIER_SETTINGS_IN_DELIVERY_OPTIONS = [
        CarrierSettings::ALLOW_DELIVERY_OPTIONS,
        CarrierSettings::ALLOW_EVENING_DELIVERY,
        CarrierSettings::ALLOW_MONDAY_DELIVERY,
        CarrierSettings::ALLOW_MORNING_DELIVERY,
        CarrierSettings::ALLOW_ONLY_RECIPIENT,
        CarrierSettings::ALLOW_PICKUP_LOCATIONS,
        CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
        CarrierSettings::ALLOW_SATURDAY_DELIVERY,
        CarrierSettings::ALLOW_SIGNATURE,
        CarrierSettings::DEFAULT_PACKAGE_TYPE,
        CarrierSettings::DIGITAL_STAMP_DEFAULT_WEIGHT,
        CarrierSettings::PRICE_EVENING_DELIVERY,
        CarrierSettings::PRICE_MORNING_DELIVERY,
        CarrierSettings::PRICE_ONLY_RECIPIENT,
        CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
        CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
        CarrierSettings::PRICE_PICKUP,
        CarrierSettings::PRICE_SAME_DAY_DELIVERY,
        CarrierSettings::PRICE_SIGNATURE,
        CarrierSettings::PRICE_STANDARD_DELIVERY,
    ];

    public    $attributes = [
        'apiBaseUrl'                 => 'api.myparcel.nl',
        'currency'                   => 'EUR',
        'packageType'                => DeliveryOptionsModel::DEFAULT_PACKAGE_TYPE_NAME,
        'locale'                     => 'nl-NL',
        'platform'                   => Platform::MYPARCEL_NAME, // TODO get from pdk / plugin
        'basePrice'                  => 0,
        'showPriceSurcharge'         => 0,
        'pickupLocationsDefaultView' => CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW_VALUE,
        'priceStandardDelivery'      => 0,
        'carrierSettings'            => [],
    ];

    protected $casts      = [
        'apiBaseUrl'                 => 'string',
        'currency'                   => 'string',
        'packageType'                => 'string',
        'locale'                     => 'string',
        'platform'                   => 'string',
        'basePrice'                  => 'integer',
        'showPriceSurcharge'         => 'boolean',
        'pickupLocationsDefaultView' => 'string',
        'priceStandardDelivery'      => 'integer',
        'carrierSettings'            => 'array',
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->apiBaseUrl = Pdk::get('apiUrl');

        if (isset($data['order'])) {
            $this->fill([
                'packageType'                => $data['order']->getDeliveryOptions()->packageType,
                'basePrice'                  => $data['order']->shipmentPriceAfterVat,
                'showPriceSurcharge'         => Settings::get('checkout.showPriceSurcharge'),
                'pickupLocationsDefaultView' => Settings::get('checkout.pickupLocationsDefaultView'),
                'carrierSettings'            => $this->createAllCarrierSettings(),
            ]);
            unset($data['order']);
        }

        parent::__construct($data);
    }

    /**
     * @return array
     */
    private function createAllCarrierSettings(): array
    {
        /** @var \MyParcelNL\Pdk\Settings\Collection\CarrierSettingsCollection $settingsCollection */
        $settingsCollection = Settings::get(CarrierSettings::ID);

        return $settingsCollection->reduce(
            function (array $acc, CarrierSettings $carrierSettings) {
                $carrierName = $carrierSettings[CarrierSettings::CARRIER_NAME];

                if (! $carrierName) {
                    return $acc;
                }

                $acc[$carrierName] = $this->createCarrierSettings($carrierSettings);

                return $acc;
            },
            []
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings $carrierSettings
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function createCarrierSettings(CarrierSettings $carrierSettings): array
    {
        $settings                          = $carrierSettings->only(self::CARRIER_SETTINGS_IN_DELIVERY_OPTIONS);
        $settings['allowShowDeliveryDate'] = $carrierSettings->featureShowDeliveryDate;

        if (! $carrierSettings->dropOffPossibilities) {
            return $settings;
        }

        return $settings
            + $this->createDropOffData($carrierSettings)
            + [
                'deliveryDaysWindow' => $carrierSettings->dropOffPossibilities->deliveryDaysWindow,
                'dropOffDelay'       => $carrierSettings->dropOffPossibilities->dropOffDelay,
            ];
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings $carrierSettings
     *
     * @return array
     */
    private function createDropOffData(CarrierSettings $carrierSettings): array
    {
        $array = [];

        $carrierSettings->dropOffPossibilities->dropOffDays->each(function (DropOffDay $dropOffDay) use (&$array) {
            if (! $dropOffDay->dispatch) {
                return;
            }

            $array['dropOffDays'][] = $dropOffDay->weekday;

            if ($dropOffDay->cutoffTime) {
                $key = (DropOffDay::WEEKDAY_SATURDAY === $dropOffDay->weekday)
                    ? 'saturdayCutoffTime'
                    : 'cutoffTime';

                $array[$key] = $dropOffDay->cutoffTime;
            }

            if ($dropOffDay->sameDayCutoffTime) {
                $array['cutoffTimeSameDay'] = $dropOffDay->sameDayCutoffTime;
            }
        });

        return $array;
    }
}
