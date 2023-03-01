<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Plugin\Service\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * @property bool   $allowRetry
 * @property string $apiBaseUrl
 * @property int    $basePrice
 * @property array  $carrierSettings
 * @property string $currency
 * @property bool   $isUsingSplitAddressFields
 * @property string $locale
 * @property string $packageType
 * @property string $pickupLocationsDefaultView
 * @property string $platform
 * @property int    $priceDeliveryTypeTypeStandard
 * @property bool   $showPriceSurcharge
 */
class DeliveryOptionsConfig extends Model
{
    public    $attributes = [
        'allowRetry'                 => false,
        'basePrice'                  => 0,
        'carrierSettings'            => [],
        'currency'                   => 'EUR',
        'locale'                     => null,
        'packageType'                => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
        'pickupLocationsDefaultView' => null,
        'platform'                   => null,
        'showPriceSurcharge'         => 0,
    ];

    protected $casts      = [
        'allowRetry'                 => 'boolean',
        'basePrice'                  => 'integer',
        'carrierSettings'            => 'array',
        'currency'                   => 'string',
        'locale'                     => 'string',
        'packageType'                => 'string',
        'pickupLocationsDefaultView' => 'string',
        'platform'                   => 'string',
        'showPriceSurcharge'         => 'boolean',
    ];

    /**
     * @param  null|array{cart: \MyParcelNL\Pdk\Plugin\Model\PdkCart} $data
     */
    public function __construct(?array $data = null)
    {
        $this->locale     = LanguageService::getLanguage();
        $this->apiBaseUrl = Pdk::get('apiUrl');
        $this->platform   = Pdk::get('platform');

        if (isset($data['cart'])) {
            /** @var \MyParcelNL\Pdk\Plugin\Service\DeliveryOptionsServiceInterface $service */
            $service = Pdk::get(DeliveryOptionsServiceInterface::class);

            [$packageType, $carrierSettings] = $service->createAllCarrierSettings($data['cart']);

            $getCheckoutSetting = static function (string $key) {
                return Settings::get($key, CheckoutSettings::ID);
            };

            $isUsingSplitAddressFields  = $getCheckoutSetting(CheckoutSettings::USE_SEPARATE_ADDRESS_FIELDS);
            $priceType                  = $getCheckoutSetting(CheckoutSettings::PRICE_TYPE);
            $pickupLocationsDefaultView = $getCheckoutSetting(CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW);

            $this->fill([
                'packageType'                => $packageType,
                'basePrice'                  => $data['cart']->shipmentPrice,
                'isUsingSplitAddressFields'  => $isUsingSplitAddressFields,
                'showPriceSurcharge'         => CheckoutSettings::PRICE_TYPE_EXCLUDED === $priceType,
                'pickupLocationsDefaultView' => $pickupLocationsDefaultView,
                'carrierSettings'            => $carrierSettings,
            ]);

            unset($data['cart']);
        }

        parent::__construct($data);
    }
}
