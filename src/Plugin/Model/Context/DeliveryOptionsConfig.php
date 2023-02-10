<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Exception\InvalidCastException;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions as DeliveryOptionsModel;
use MyParcelNL\WooCommerce\Pdk\Service\WcTaxService;

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
    private const CONFIG_CARRIER_SETTINGS_MAP = [
        'allowDeliveryOptions'         => CarrierSettings::ALLOW_DELIVERY_OPTIONS,
        'allowEveningDelivery'         => CarrierSettings::ALLOW_EVENING_DELIVERY,
        'allowMondayDelivery'          => CarrierSettings::ALLOW_MONDAY_DELIVERY,
        'allowMorningDelivery'         => CarrierSettings::ALLOW_MORNING_DELIVERY,
        'allowOnlyRecipient'           => CarrierSettings::ALLOW_ONLY_RECIPIENT,
        'allowPickupLocations'         => CarrierSettings::ALLOW_PICKUP_LOCATIONS,
        'allowSameDayDelivery'         => CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
        'allowSaturdayDelivery'        => CarrierSettings::ALLOW_SATURDAY_DELIVERY,
        'allowSignature'               => CarrierSettings::ALLOW_SIGNATURE,
        'featureShowDeliveryDate'      => CarrierSettings::SHOW_DELIVERY_DAY,
        'priceEveningDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_EVENING,
        'priceMorningDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_MORNING,
        'priceOnlyRecipient'           => CarrierSettings::PRICE_ONLY_RECIPIENT,
        'pricePackageTypeDigitalStamp' => CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
        'pricePackageTypeMailbox'      => CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
        'pricePickup'                  => CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP,
        'priceSameDayDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY,
        'priceSignature'               => CarrierSettings::PRICE_SIGNATURE,
        'priceStandardDelivery'        => CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD,
    ];

    public    $attributes = [
        'allowRetry'                 => false,
        'basePrice'                  => 0,
        'carrierSettings'            => [],
        'currency'                   => 'EUR',
        'locale'                     => null,
        'packageType'                => DeliveryOptionsModel::DEFAULT_PACKAGE_TYPE_NAME,
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
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function __construct(?array $data = null)
    {
        $this->locale     = LanguageService::getLanguage();
        $this->apiBaseUrl = Pdk::get('apiUrl');
        $this->platform   = Pdk::get('platform');

        if (isset($data['cart'])) {
            /** @var \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart */
            $cart = $data['cart'];

            $this->fill([
                'packageType'                => $cart->shippingMethod->preferPackageType,
                'basePrice'                  => $cart->shipmentPrice,
                'isUsingSplitAddressFields'  => Settings::get(
                    CheckoutSettings::USE_SEPARATE_ADDRESS_FIELDS,
                    CheckoutSettings::ID
                ),
                'showPriceSurcharge'         => Settings::get(
                        CheckoutSettings::PRICE_TYPE,
                        CheckoutSettings::ID
                    ) === CheckoutSettings::PRICE_TYPE_EXCLUDED,
                'pickupLocationsDefaultView' => Settings::get(
                    CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW,
                    CheckoutSettings::ID
                ),
                'carrierSettings'            => $this->createAllCarrierSettings($cart),
            ]);

            unset($data['cart']);
        }

        parent::__construct($data);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $pdkCart
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function createAllCarrierSettings(PdkCart $pdkCart): array
    {
        if (! $pdkCart->shippingMethod->hasDeliveryOptions) {
            return [];
        }

        $settings = [];

        $carrierOptions = AccountSettings::getCarrierOptions();

        foreach ($carrierOptions->all() as $carrierOption) {
            // TODO: make sure only carriers that should be in the frontend are
            //  shown. Now we'll get carriers like "bol.com" in the frontend.
            //  Checking if the capabilities are empty in this crude way works,
            //  but it's not ideal.
            $hasNoCapabilities = empty(Arr::flatten($carrierOption->capabilities->toArrayWithoutNull()));

            if (! $carrierOption->carrier->enabled || $hasNoCapabilities) {
                continue;
            }

            $settings[$carrierOption->carrier->externalIdentifier] = $this->createCarrierSettings(
                $carrierOption,
                $pdkCart
            );
        }

        return $settings;
    }

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\CarrierOptions $carrierOptions
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart         $pdkCart
     *
     * @return array
     */
    private function createCarrierSettings(CarrierOptions $carrierOptions, PdkCart $pdkCart): array
    {
        //        $cartWeight    = $pdkCart->lines->reduce(function (float $carry, PdkOrderLine $line) {
        //            return $carry + $line->product->weight * $line->quantity;
        //        }, 0);
        //        $mailboxWeight = Settings::get('empty_mailbox_weight', CheckoutSettings::ID) + $cartWeight;
        //        $packageWeight = Settings::get('empty_package_weight', CheckoutSettings::ID) + $cartWeight;
        //
        //        TODO check if the preferred package type is allowed regarding the weight by this carrier
        //        $preferPackageType   = $pdkCart->shippingMethod->preferPackageType;
        //        $allowPackageTypes   = $pdkCart->shippingMethod->allowPackageTypes;
        $minimumDropOffDelay = $pdkCart->shippingMethod->minimumDropOffDelay;

        $carrierSettings = new CarrierSettings(
            Settings::get(sprintf('%s.%s', CarrierSettings::ID, $carrierOptions->carrier->externalIdentifier))
        );

        $dropOff = $carrierSettings->dropOffPossibilities->getForDate();
        $taxService = new WcTaxService();

        $settings = array_map(static function ($key) use ($carrierSettings, $taxService) {
            try {
                $value = $carrierSettings->getAttribute($key);
            } catch (InvalidCastException $e) {
                return null;
            }
            if (0 === strpos($key, 'price')) {
                return $taxService->getShippingDisplayPrice((float) $value);
            }
            return $value;
        }, self::CONFIG_CARRIER_SETTINGS_MAP);

        return $settings + [
                'deliveryDaysWindow'   => $carrierSettings->dropOffPossibilities->deliveryDaysWindow,
                'dropOffDelay'         => max(
                    $minimumDropOffDelay,
                    $carrierSettings->dropOffPossibilities->dropOffDelay
                ),
                'allowSameDayDelivery' => ($settings['allowSameDayDelivery'] ?? false) && 0 === $minimumDropOffDelay,
                'cutoffTime'           => $dropOff->cutoffTime ?? '17:00',
                'cutoffTimeSameDay'    => $dropOff->sameDayCutoffTime ?? '10:00',
            ];
    }

    //    /**
    //     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings $carrierSettings
    //     *
    //     * @return array
    //     */
    //    private function createDropOffData(CarrierSettings $carrierSettings): array
    //    {
    //        $array = [];
    //
    //        $carrierSettings->dropOffPossibilities->dropOffDays->each(function (DropOffDay $dropOffDay) use (&$array) {
    //            if (! $dropOffDay->dispatch) {
    //                return;
    //            }
    //
    //            $array['dropOffDays'][] = $dropOffDay->weekday;
    //
    //            if ($dropOffDay->cutoffTime) {
    //                $key = (DropOffDay::WEEKDAY_SATURDAY === $dropOffDay->weekday)
    //                    ? 'saturdayCutoffTime'
    //                    : 'cutoffTime';
    //
    //                $array[$key] = $dropOffDay->cutoffTime;
    //            }
    //
    //            if ($dropOffDay->sameDayCutoffTime) {
    //                $array['cutoffTimeSameDay'] = $dropOffDay->sameDayCutoffTime;
    //            }
    //        });
    //
    //        return $array;
    //    }
}
