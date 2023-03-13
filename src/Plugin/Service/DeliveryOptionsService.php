<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\AccountSettings;
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
     * @var \MyParcelNL\Pdk\Shipment\Service\DropOffServiceInterface
     */
    private $dropOffService;

    /**
     * @var \MyParcelNL\Pdk\Validation\Repository\SchemaRepository
     */
    private $schemaRepository;

    /**
     * @var \MyParcelNL\Pdk\Plugin\Service\TaxServiceInterface
     */
    private $taxService;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Service\DropOffServiceInterface $dropOffService
     * @param  \MyParcelNL\Pdk\Plugin\Service\TaxServiceInterface       $taxService
     * @param  \MyParcelNL\Pdk\Validation\Repository\SchemaRepository   $schemaRepository
     */
    public function __construct(
        DropOffServiceInterface $dropOffService,
        TaxServiceInterface     $taxService,
        SchemaRepository        $schemaRepository
    ) {
        $this->dropOffService   = $dropOffService;
        $this->taxService       = $taxService;
        $this->schemaRepository = $schemaRepository;
    }

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

        [$packageType, $carrierOptions] = $this->getValidCarrierOptions($cart);

        $settings = [
            'packageType'     => $packageType,
            'carrierSettings' => [],
        ];

        /** @var CarrierOptions $carrierOption */
        foreach ($carrierOptions->all() as $carrierOption) {
            $identifier                               = $carrierOption->carrier->externalIdentifier;
            $settings['carrierSettings'][$identifier] = $this->createCarrierSettings($carrierOption, $cart);
        }

        return $settings;
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
        $carrierSettings = new CarrierSettings(
            Settings::get(sprintf('%s.%s', CarrierSettings::ID, $carrierOptions->carrier->externalIdentifier))
        );

        $dropOff             = $this->dropOffService->getForDate($carrierSettings);
        $minimumDropOffDelay = $cart->shippingMethod->minimumDropOffDelay;

        return array_merge(
            $this->getBaseSettings($carrierSettings),
            [
                'deliveryDaysWindow'   => $carrierSettings->deliveryDaysWindow,
                'dropOffDelay'         => max($minimumDropOffDelay, $carrierSettings->dropOffDelay),
                'allowSameDayDelivery' => ($settings['allowSameDayDelivery'] ?? false) && 0 === $minimumDropOffDelay,
                'cutoffTime'           => $dropOff->cutoffTime ?? null,
                'cutoffTimeSameDay'    => $dropOff->sameDayCutoffTime ?? null,
            ]
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings $carrierSettings
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getBaseSettings(CarrierSettings $carrierSettings): array
    {
        return array_map(function ($key) use ($carrierSettings) {
            $value = $carrierSettings->getAttribute($key);

            if (Str::startsWith($key, 'price')) {
                return $this->taxService->getShippingDisplayPrice((float) $value);
            }

            return $value;
        }, self::CONFIG_CARRIER_SETTINGS_MAP);
    }

    /**
     * Filters all carrier options by the package type and weight.
     *
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return array
     */
    private function getValidCarrierOptions(PdkCart $cart): array
    {
        $packageType    = DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME;
        $carrierOptions = AccountSettings::getCarrierOptions();

        foreach ($cart->shippingMethod->allowPackageTypes as $allowedPackageType) {
            $weight = $this->getWeightByPackageType($cart, $allowedPackageType);

            $filteredCarrierOptions = $carrierOptions->filter(
                function (CarrierOptions $carrierOptions) use ($cart, $weight, $allowedPackageType) {
                    return ($this->schemaRepository->validateOption(
                        $this->schemaRepository->getOrderValidationSchema(
                            $carrierOptions->carrier->name,
                            $cart->shippingMethod->shippingAddress->cc,
                            $allowedPackageType
                        ),
                        OrderPropertiesValidator::WEIGHT_KEY,
                        $weight
                    ));
                }
            );

            if ($filteredCarrierOptions->isNotEmpty()) {
                $packageType    = $allowedPackageType;
                $carrierOptions = $filteredCarrierOptions;
                break;
            }
        }

        return [$packageType, $carrierOptions];
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     * @param  string                               $packageType
     *
     * @return int
     */
    private function getWeightByPackageType(PdkCart $cart, string $packageType): int
    {
        $cartWeight = $cart->lines->reduce(function (float $carry, PdkOrderLine $line) {
            return $carry + $line->product->weight * $line->quantity;
        }, 0);

        $weight = 1;

        switch ($packageType) {
            case DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME:
                $weight = Settings::get(OrderSettings::EMPTY_MAILBOX_WEIGHT, OrderSettings::ID) + $cartWeight;
                break;

            case DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME:
                $weight = Settings::get(OrderSettings::EMPTY_DIGITAL_STAMP_WEIGHT, OrderSettings::ID) + $cartWeight;
                break;
        }

        return $weight;
    }
}
