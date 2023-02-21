<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Exception\InvalidCastException;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Model\PdkOrderLine;
use MyParcelNL\Pdk\Plugin\Service\TaxService;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Service\DropOffServiceInterface;
use MyParcelNL\Pdk\Validation\Repository\SchemaRepository;
use MyParcelNL\Pdk\Validation\Validator\OrderPropertiesValidator;

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

        if (isset($data['cart'])) {
            /** @var \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart */
            $cart = $data['cart'];
            [$packageType, $settings] = $this->createAllCarrierSettings($cart);

            $this->fill([
                'packageType'                => $packageType,
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
                'carrierSettings'            => $settings,
            ]);

            unset($data['cart']);
        }

        parent::__construct($data);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $pdkCart
     *
     * @return array
     */
    private function createAllCarrierSettings(PdkCart $pdkCart): array
    {
        if (! $pdkCart->shippingMethod->hasDeliveryOptions) {
            return [];
        }

        $settings = [];
        [$packageType, $carrierOptions] = $this->getValidCarrierOptions($pdkCart);

        foreach ($carrierOptions->all() as $carrierOption) {
            $settings[$carrierOption->carrier->getIdentifier()] = $this->createCarrierSettings(
                $carrierOption,
                $pdkCart
            );
        }

        return [$packageType, $settings];
    }

    private function getValidCarrierOptions(PdkCart $pdkCart): array
    {
        $cartWeight         = $pdkCart->lines->reduce(function (float $carry, PdkOrderLine $line) {
            return $carry + $line->product->weight * $line->quantity;
        }, 0);
        $digitalStampWeight = Settings::get(
                OrderSettings::EMPTY_DIGITAL_STAMP_WEIGHT,
                OrderSettings::ID
            ) + $cartWeight;
        $mailboxWeight      = Settings::get(OrderSettings::EMPTY_MAILBOX_WEIGHT, OrderSettings::ID) + $cartWeight;
        $cc                 = $pdkCart->shippingMethod->shippingAddress->cc;
        $allowPackageTypes  = $pdkCart->shippingMethod->allowPackageTypes;

        filterOptionsByPackageType:
        $packageType = reset($allowPackageTypes) ?: DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME;
        switch ($packageType) {
            case DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME:
                $weight = 1;
                break;
            case DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME:
                $weight = $mailboxWeight;
                break;
            case DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME:
                $weight = $digitalStampWeight;
        }

        $carrierOptions = AccountSettings::getCarrierOptions()
            ->filter(function (CarrierOptions $carrierOptions) use (
                $weight,
                $packageType,
                $cc
            ) {
                $carrier = $carrierOptions->carrier;
                $repo    = Pdk::get(SchemaRepository::class);
                $schema  = $repo->getOrderValidationSchema($carrier->name, $cc, $packageType);

                return ($repo->validateOption($schema, OrderPropertiesValidator::WEIGHT_KEY, (int) $weight));
            });

        if ($carrierOptions->isEmpty()
            && DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME !== array_shift(
                $allowPackageTypes
            )) {
            goto filterOptionsByPackageType;
        }

        return [$packageType, $carrierOptions];
    }

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\CarrierOptions $carrierOptions
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart         $pdkCart
     *
     * @return array
     */
    private function createCarrierSettings(CarrierOptions $carrierOptions, PdkCart $pdkCart): array
    {
        $minimumDropOffDelay = $pdkCart->shippingMethod->minimumDropOffDelay;

        $carrierSettings = new CarrierSettings(
            Settings::get(sprintf('%s.%s', CarrierSettings::ID, $carrierOptions->carrier->getIdentifier()))
        );

        /** @var \MyParcelNL\Pdk\Shipment\Service\DropOffServiceInterface $dropOffService */
        $dropOffService = Pdk::get(DropOffServiceInterface::class);
        $dropOff        = $dropOffService->getForDate($carrierSettings);

        return $this->getBaseSettings($carrierSettings) + [
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

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings $carrierSettings
     *
     * @return array
     */
    private function getBaseSettings(CarrierSettings $carrierSettings): array
    {
        /** @var TaxService $taxService */
        $taxService = Pdk::get(TaxService::class);

        return array_map(static function ($key) use ($carrierSettings, $taxService) {
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
    }
}
