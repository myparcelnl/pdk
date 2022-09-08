<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Settings\Adapter\DeliveryOptionsConfigAdapter;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;

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
        'apiBaseUrl'                 => 'api.myparcel.nl',
        'currency'                   => 'EUR',
        'packageType'                => null,
        'locale'                     => 'nl-NL',
        'platform'                   => null,
        'basePrice'                  => null,
        'showPriceSurcharge'         => null,
        'pickupLocationsDefaultView' => null,
        'priceStandardDelivery'      => null,
        'carrierSettings'            => null,
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
        'carrierSettings'            => 'array',//CarrierSettingsCollection::class
    ];

    public function __construct($data)
    {
        parent::__construct($data);

        if (! isset($data['order'])) {
            return;
        }

        $this->fillOrderData($data['order']);
    }

    private function fillOrderData(PdkOrder $order): void
    {
        $this->attributes['packageType']                = $order->getDeliveryOptions()->packageType;
        $this->attributes['platform']                   = Platform::MYPARCEL_NAME; // TODO get from pdk / plugin
        $this->attributes['basePrice']                  = $order->orderTotals->shipmentPriceAfterVat;
        $this->attributes['showPriceSurcharge']         = Settings::get('checkout.showPriceSurcharge');
        $this->attributes['pickupLocationsDefaultView'] = Settings::get('checkout.pickupLocationsDefaultView');

        $carrierSettings = Settings::get(CarrierSettings::ID);

        $this->attributes['carrierSettings'] = (new DeliveryOptionsConfigAdapter($carrierSettings))->toArray();

        unset ($this->attributes['order']);
    }
}
