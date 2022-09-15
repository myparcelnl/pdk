<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Pdk as BasePdk;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Settings\Collection\CarrierSettingsCollection;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
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
class DeliveryOptionsConfig extends Model
{
    public    $attributes = [
        'apiBaseUrl'                 => BasePdk::DEFAULT_API_URL,
        'currency'                   => 'EUR',
        'packageType'                => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
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

    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        $this->apiBaseUrl = Pdk::getApiBaseUrl();

        if (! isset($data['order'])) {
            return;
        }

        $this->fillOrderData($data['order']);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return void
     */
    private function fillOrderData(PdkOrder $order): void
    {
        $this->attributes['packageType']                = $order->getDeliveryOptions()->packageType;
        $this->attributes['basePrice']                  = $order->getShipmentPriceAfterVat();
        $this->attributes['showPriceSurcharge']         = Settings::get('checkout.showPriceSurcharge');
        $this->attributes['pickupLocationsDefaultView'] = Settings::get('checkout.pickupLocationsDefaultView');

        $carrierSettings = Settings::get(CarrierSettings::ID);

        $this->attributes['carrierSettings'] = $this->adapt($carrierSettings);

        unset($this->attributes['order']);
    }

    private function adapt(CarrierSettingsCollection $settingsCollection): array
    {
        $adapted = [];

        foreach ($settingsCollection as $carrierSettings) {
            if (! $carrierSettings[CarrierSettings::CARRIER_NAME]) {
                continue;
            }

            $adapted[$carrierSettings[CarrierSettings::CARRIER_NAME]] = $this->adaptSettings(
                $carrierSettings->toArray()
            );
        }

        return $adapted;
    }

    private function adaptSettings(array $settings): array
    {
        $dropOffPossibilities = $settings[CarrierSettings::DROP_OFF_POSSIBILITIES];
        $adapted              = [];

        if (! $dropOffPossibilities) {
            return $settings;
        }

        $adapted['allowShowDeliveryDate'] = $settings['featureShowDeliveryDate'];
        $adapted['deliveryDaysWindow']    = $dropOffPossibilities['deliveryDaysWindow'];
        $adapted['dropOffDelay']          = $dropOffPossibilities['dropOffDelay'];
        $adapted['dropOffDays']           = [];
        $adapted['cutoffTime']            = '16:30';
        $adapted['cutoffTimeSameDay']     = '10:00';
        $adapted['saturdayCutoffTime']    = '15:00';

        foreach ($dropOffPossibilities['dropOffDays'] as $dropOffDay) {
            if (! $dropOffDay['dispatch']) {
                continue;
            }

            $weekday                  = $dropOffDay['weekday'];
            $adapted['dropOffDays'][] = $weekday;

            if (isset($dropOffDay['cutoffTime'])) {
                $key = (DropOffDay::WEEKDAY_SATURDAY === $weekday) ? 'saturdayCutoffTime' : 'cutoffTime';
                $adapted[$key] = $dropOffDay['cutoffTime'];
            }

            if (isset($dropOffDay['sameDayCutoffTime'])) {
                $adapted['cutoffTimeSameDay'] = $dropOffDay['sameDayCutoffTime'];
            }
        }

        return $settings + $adapted;
    }
}
