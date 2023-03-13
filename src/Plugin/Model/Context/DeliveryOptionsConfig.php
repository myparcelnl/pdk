<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
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
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->locale     = LanguageService::getLanguage();
        $this->apiBaseUrl = Pdk::get('apiUrl');
        $this->platform   = Pdk::get('platform');

        parent::__construct($data);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return self
     */
    public static function fromCart(PdkCart $cart): self
    {
        /** @var \MyParcelNL\Pdk\Plugin\Service\DeliveryOptionsServiceInterface $service */
        $service = Pdk::get(DeliveryOptionsServiceInterface::class);

        $getCheckoutSetting = static function (string $key) {
            return Settings::get($key, CheckoutSettings::ID);
        };

        $priceType = $getCheckoutSetting(CheckoutSettings::PRICE_TYPE);

        return new self(
            array_merge($service->createAllCarrierSettings($cart), [
                'basePrice'                  => $cart->shipmentPrice,
                'isUsingSplitAddressFields'  => $getCheckoutSetting(CheckoutSettings::USE_SEPARATE_ADDRESS_FIELDS),
                'pickupLocationsDefaultView' => $getCheckoutSetting(CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW),
                'showPriceSurcharge'         => CheckoutSettings::PRICE_TYPE_EXCLUDED === $priceType,
            ])
        );
    }
}
