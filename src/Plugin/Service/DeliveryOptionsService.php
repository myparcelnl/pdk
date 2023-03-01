<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Model\PdkOrderLine;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Service\DropOffServiceInterface;
use MyParcelNL\Pdk\Validation\Repository\SchemaRepository;
use MyParcelNL\Pdk\Validation\Validator\OrderPropertiesValidator;
use MyParcelNL\Sdk\src\Support\Str;

class DeliveryOptionsService implements DeliveryOptionsServiceInterface
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

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function createAllCarrierSettings(PdkCart $cart): array
    {
        if (! $cart->shippingMethod->hasDeliveryOptions) {
            return [];
        }

        $settings = [];

        [$packageType, $carrierOptions] = $this->getValidCarrierOptions($cart);

        /** @var CarrierOptions $carrierOption */
        foreach ($carrierOptions->all() as $carrierOption) {
            $identifier            = $carrierOption->carrier->externalIdentifier;
            $settings[$identifier] = $this->createCarrierSettings($carrierOption, $cart);
        }

        return [$packageType, $settings];
    }

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\CarrierOptions $carrierOptions
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart         $cart
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function createCarrierSettings(CarrierOptions $carrierOptions, PdkCart $cart): array
    {
        $minimumDropOffDelay = $cart->shippingMethod->minimumDropOffDelay;

        $carrierSettings = new CarrierSettings(
            Settings::get(sprintf('%s.%s', CarrierSettings::ID, $carrierOptions->carrier->externalIdentifier))
        );

        /** @var \MyParcelNL\Pdk\Shipment\Service\DropOffServiceInterface $dropOffService */
        $dropOffService = Pdk::get(DropOffServiceInterface::class);

        $dropOff = $dropOffService->getForDate($carrierSettings);

        return $this->getBaseSettings($carrierSettings) + [
                'deliveryDaysWindow'   => $carrierSettings->deliveryDaysWindow,
                'dropOffDelay'         => max($minimumDropOffDelay, $carrierSettings->dropOffDelay),
                'allowSameDayDelivery' => ($settings['allowSameDayDelivery'] ?? false) && 0 === $minimumDropOffDelay,
                'cutoffTime'           => $dropOff->cutoffTime ?? null,
                'cutoffTimeSameDay'    => $dropOff->sameDayCutoffTime ?? null,
            ];
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings $carrierSettings
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getBaseSettings(CarrierSettings $carrierSettings): array
    {
        /** @var TaxService $taxService */
        $taxService = Pdk::get(TaxService::class);

        return array_map(static function ($key) use ($carrierSettings, $taxService) {
            $value = $carrierSettings->getAttribute($key);

            if (Str::startsWith($key, 'price')) {
                return $taxService->getShippingDisplayPrice((float) $value);
            }

            return $value;
        }, self::CONFIG_CARRIER_SETTINGS_MAP);
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return array
     */
    private function getValidCarrierOptions(PdkCart $cart): array
    {
        $cartWeight = $cart->lines->reduce(function (float $carry, PdkOrderLine $line) {
            return $carry + $line->product->weight * $line->quantity;
        }, 0);

        $digitalStampWeight = Settings::get(OrderSettings::EMPTY_DIGITAL_STAMP_WEIGHT, OrderSettings::ID) + $cartWeight;
        $mailboxWeight      = Settings::get(OrderSettings::EMPTY_MAILBOX_WEIGHT, OrderSettings::ID) + $cartWeight;

        $cc                = $cart->shippingMethod->shippingAddress->cc;
        $allowPackageTypes = $cart->shippingMethod->allowPackageTypes;

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
            ->filter(function (CarrierOptions $carrierOptions) use ($weight, $packageType, $cc) {
                $carrier = $carrierOptions->carrier;
                $repo    = Pdk::get(SchemaRepository::class);
                $schema  = $repo->getOrderValidationSchema($carrier->name, $cc, $packageType);

                return ($repo->validateOption($schema, OrderPropertiesValidator::WEIGHT_KEY, (int) $weight));
            });

        if ($carrierOptions->isEmpty()
            && DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME !== array_shift($allowPackageTypes)) {
            goto filterOptionsByPackageType;
        }

        return [$packageType, $carrierOptions];
    }
}
