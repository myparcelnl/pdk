<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions as DeliveryOptionsModel;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;

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
        CarrierSettings::PRICE_DELIVERY_TYPE_EVENING,
        CarrierSettings::PRICE_DELIVERY_TYPE_MORNING,
        CarrierSettings::PRICE_ONLY_RECIPIENT,
        CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
        CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
        CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP,
        CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY,
        CarrierSettings::PRICE_SIGNATURE,
        CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD,
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
                'packageType'                => $cart->shippingMethod->packageType,
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
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function createAllCarrierSettings(PdkCart $cart): array
    {
        if ($cart->shippingMethod->disableDeliveryOptions) {
            return [];
        }

        $carrierOptions      = AccountSettings::getCarrierOptions();
        $carrierConfigs      = [];
        $cartWeight          = $cart->physicalProperties->weight;
        $mailboxWeight       = Settings::get('empty_mailbox_weight', CheckoutSettings::ID) + $cartWeight;
        $packageWeight       = Settings::get('empty_package_weight', CheckoutSettings::ID) + $cartWeight;
        $preferPackageType   = $cart->shippingMethod->preferPackageType;
        $allowPackageTypes   = $cart->shippingMethod->allowPackageTypes;
        $minimumDropOffDelay = $cart->shippingMethod->minimumDropOffDelay;
        // check with the carrier schema (weights...) if the package type is allowed
        // in de config packagetype, en per carrier dropoffdelay moet rekening houden met de cart
        $carrierOptions->each(function (CarrierOptions $carrierOptions) use (&$carrierConfigs) {
            if (! $carrierOptions->carrier->enabled || $carrierOptions->capabilities->isEmpty()) {
                return;
            }

            // todo get carrier settings from db, update with info from the products / lines
            $settings = new CarrierSettings([
                'carrierName'          => $carrierOptions->carrier->name,
                'allowDeliveryOptions' => true,
            ]);

            $carrierConfigs[$carrierOptions->carrier->name] = $this->createCarrierSettings($settings);
        });

        //        $carrierSettings = (new CarrierSettingsCollection([
        //            new CarrierSettings([
        //                'carrierName'          => 'postnl',
        //                'allowDeliveryOptions' => true,
        //            ]),
        //        ]));

        //        $carrierOptions->each(function (CarrierOptions $carrierOption) use ($carrierSettings, &$array) {
        //            $settings = $carrierSettings->filter(function (CarrierSettings $carrierSettings) use ($carrierOption) {
        //                return $carrierOption->carrier->name === $carrierSettings->carrierName;
        //            });
        //
        //            if ($settings->isEmpty()) {
        //                return;
        //            }
        //
        //            $array[$carrierOption->carrier->name] = $this->createCarrierSettings($settings->first());
        //        });

        return $carrierConfigs;
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
        $settings['allowShowDeliveryDate'] = $carrierSettings->getShowDeliveryDay();

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
