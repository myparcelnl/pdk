<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\PackageType;
use MyParcelNL\Pdk\Validation\Repository\SchemaRepository;
use MyParcelNL\Pdk\Validation\Validator\OrderPropertiesValidator;
use MyParcelNL\Sdk\src\Support\Str;

class DeliveryOptionsService implements DeliveryOptionsServiceInterface
{
    private const PACKAGE_TYPE_EMPTY_WEIGHT_MAP = [
        DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME       => OrderSettings::EMPTY_PARCEL_WEIGHT,
        DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME       => OrderSettings::EMPTY_MAILBOX_WEIGHT,
        DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME => OrderSettings::EMPTY_DIGITAL_STAMP_WEIGHT,
    ];
    private const CONFIG_CARRIER_SETTINGS_MAP   = [
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

    public function __construct(
        private readonly DropOffServiceInterface  $dropOffService,
        private readonly TaxServiceInterface      $taxService,
        private readonly SchemaRepository         $schemaRepository,
        private readonly CurrencyServiceInterface $currencyService
    ) {
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function createAllCarrierSettings(PdkCart $cart): array
    {
        if (! $cart->shippingMethod->hasDeliveryOptions) {
            return [];
        }

        [$packageType, $carriers] = $this->getValidCarrierOptions($cart);

        $showPriceSurcharge =
            Settings::get(CheckoutSettings::PRICE_TYPE, CheckoutSettings::ID) === CheckoutSettings::PRICE_TYPE_INCLUDED;

        $settings = [
            'packageType'           => $packageType,
            'carrierSettings'       => [],
            'basePrice'             => $this->currencyService->convertToEuros($cart->shipmentPrice),
            'priceStandardDelivery' => $showPriceSurcharge ? $cart->shipmentPrice : 0,
        ];

        foreach ($carriers->all() as $carrier) {
            $identifier                               = $carrier->externalIdentifier;
            $settings['carrierSettings'][$identifier] =
                $this->createCarrierSettings($carrier, $cart);
        }

        return $settings;
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function createCarrierSettings(Carrier $carrier, PdkCart $cart): array
    {
        $carrierSettings = new CarrierSettings(
            Settings::get(sprintf('%s.%s', CarrierSettings::ID, $carrier->externalIdentifier))
        );

        $dropOff           = $this->dropOffService->getForDate($carrierSettings);
        $dropOffCollection = $this->dropOffService->getPossibleDropOffDays($carrierSettings);
        $dropOffDays       = (new Collection($dropOffCollection))
            ->pluck('weekday')
            ->toArray();

        $minimumDropOffDelay = $cart->shippingMethod->minimumDropOffDelay;

        $settings = $this->getBaseSettings($carrierSettings, $cart);

        return array_merge(
            $settings,
            [
                'deliveryDaysWindow'   => $carrierSettings->deliveryDaysWindow,
                'dropOffDelay'         => max($minimumDropOffDelay, $carrierSettings->dropOffDelay),
                'allowSameDayDelivery' => ($settings['allowSameDayDelivery'] ?? false) && 0 === $minimumDropOffDelay,
                'cutoffTime'           => $dropOff->cutoffTime ?? null,
                'cutoffTimeSameDay'    => $dropOff->sameDayCutoffTime ?? null,
                'dropOffDays'          => $dropOffDays,
            ]
        );
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getBaseSettings(CarrierSettings $carrierSettings, PdkCart $cart): array
    {
        $showPriceSurcharge =
            Settings::get(CheckoutSettings::PRICE_TYPE, CheckoutSettings::ID) === CheckoutSettings::PRICE_TYPE_INCLUDED;

        return array_map(function ($key) use ($carrierSettings, $cart, $showPriceSurcharge) {
            $value = $carrierSettings->getAttribute($key);

            if (Str::startsWith($key, 'price')) {
                $subtotal = $showPriceSurcharge
                    ? $value + $this->currencyService->convertToEuros($cart->shipmentPrice)
                    : $value;

                return $this->taxService->getShippingDisplayPrice((float) $subtotal);
            }

            return $value;
        }, self::CONFIG_CARRIER_SETTINGS_MAP);
    }

    /**
     * Filters all carrier options by the package type and weight.
     *
     * @return array<string, \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection>
     */
    private function getValidCarrierOptions(PdkCart $cart): array
    {
        $allCarriers     = AccountSettings::getCarriers();
        $carrierSettings = Settings::get(CarrierSettings::ID);

        foreach ($cart->shippingMethod->allowedPackageTypes->all() as $packageType) {
            $weight = $this->getWeightByPackageType($cart, $packageType);

            $filteredCarriers = $allCarriers
                ->filter(
                    function (Carrier $carrier) use ($cart, $weight, $packageType, $carrierSettings): bool {
                        $hasDeliveryOptions =
                            $carrierSettings[$carrier->externalIdentifier][CarrierSettings::DELIVERY_OPTIONS_ENABLED] ?? false;

                        if (! $hasDeliveryOptions) {
                            return false;
                        }

                        return $this->schemaRepository->validateOption(
                            $this->schemaRepository->getOrderValidationSchema(
                                $carrier->name,
                                $cart->shippingMethod->shippingAddress->cc,
                                // TODO: support full package type class instead of string
                                $packageType->name
                            ),
                            OrderPropertiesValidator::WEIGHT_KEY,
                            $weight
                        );
                    }
                );

            if ($filteredCarriers->isNotEmpty()) {
                return [$packageType->name, $filteredCarriers];
            }
        }

        return [DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME, $allCarriers];
    }

    private function getWeightByPackageType(PdkCart $cart, PackageType $packageType): int
    {
        $cartWeight = (int) $cart->lines->reduce(
            fn(float $carry, PdkOrderLine $line) => $carry + $line->product->weight * $line->quantity,
            0
        );

        $fullWeight = $cartWeight;

        $emptyWeightSetting = self::PACKAGE_TYPE_EMPTY_WEIGHT_MAP[$packageType->name] ?? null;

        if ($emptyWeightSetting) {
            $fullWeight += Settings::get($emptyWeightSetting, OrderSettings::ID);
        }

        return $fullWeight ?: 1;
    }
}
