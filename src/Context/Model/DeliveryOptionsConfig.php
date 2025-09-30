<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

/**
 * @property bool   $allowRetry
 * @property string $apiBaseUrl
 * @property int    $basePrice
 * @property array  $carrierSettings
 * @property string $currency
 * @property string $locale
 * @property string $packageType
 * @property string $pickupLocationsDefaultView
 * @property bool   $allowPickupLocationsViewSelection
 * @property string $platform
 * @property int    $priceStandardDelivery
 * @property bool   $showPriceSurcharge
 * @property array  $closedDays
 */
class DeliveryOptionsConfig extends Model
{
    public    $attributes = [
        'allowRetry'                     => false,
        'basePrice'                      => 0,
        'carrierSettings'                => [],
        'currency'                       => 'EUR',
        'locale'                         => null,
        'packageType'                    => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
        'pickupLocationsDefaultView'     => null,
        'allowPickupLocationsViewSelection' => true,
        'platform'                       => null,
        'priceStandardDelivery'          => 0,
        'showPriceSurcharge'             => false,
        'closedDays'                     => [],
    ];

    protected $casts      = [
        'allowRetry'                     => 'boolean',
        'basePrice'                      => 'float',
        'carrierSettings'                => 'array',
        'currency'                       => 'string',
        'locale'                         => 'string',
        'packageType'                    => 'string',
        'pickupLocationsDefaultView'     => 'string',
        'allowPickupLocationsViewSelection' => 'boolean',
        'platform'                       => 'string',
        'priceStandardDelivery'          => 'float',
        'showPriceSurcharge'             => 'boolean',
        'closedDays'                     => 'array',
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->locale     = Language::getLanguage();
        $this->apiBaseUrl = Pdk::get('apiUrl');
        $this->platform   = Platform::getPropositionName();

        $priceType = Settings::get(CheckoutSettings::PRICE_TYPE, CheckoutSettings::ID);

        $this->showPriceSurcharge         = CheckoutSettings::PRICE_TYPE_EXCLUDED === $priceType;
        $this->pickupLocationsDefaultView = Settings::get(
            CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW,
            CheckoutSettings::ID
        );
        $this->allowPickupLocationsViewSelection = Settings::get(
            CheckoutSettings::ALLOW_PICKUP_LOCATIONS_VIEW_SELECTION,
            CheckoutSettings::ID
        );
        $this->closedDays = Settings::get(CheckoutSettings::CLOSED_DAYS, CheckoutSettings::ID);

        parent::__construct($data);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return self
     */
    public static function fromCart(PdkCart $cart): self
    {
        /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface $service */
        $service = Pdk::get(DeliveryOptionsServiceInterface::class);

        return new self($service->createAllCarrierSettings($cart));
    }
}
