<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Frontend\Contract\FrontendDataAdapterInterface;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\PackageType;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Validation\Repository\SchemaRepository;
use MyParcelNL\Sdk\src\Support\Str;

class DeliveryOptionsService implements DeliveryOptionsServiceInterface
{
    private const CONFIG_CARRIER_SETTINGS_MAP = [
        'allowDeliveryOptions'         => CarrierSettings::ALLOW_DELIVERY_OPTIONS,
        'allowStandardDelivery'        => CarrierSettings::ALLOW_STANDARD_DELIVERY,
        'allowEveningDelivery'         => CarrierSettings::ALLOW_EVENING_DELIVERY,
        'allowMondayDelivery'          => CarrierSettings::ALLOW_MONDAY_DELIVERY,
        'allowMorningDelivery'         => CarrierSettings::ALLOW_MORNING_DELIVERY,
        'allowOnlyRecipient'           => CarrierSettings::ALLOW_ONLY_RECIPIENT,
        'allowPickupLocations'         => CarrierSettings::ALLOW_PICKUP_DELIVERY,
        'allowSameDayDelivery'         => CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
        'allowSaturdayDelivery'        => CarrierSettings::ALLOW_SATURDAY_DELIVERY,
        'allowSignature'               => CarrierSettings::ALLOW_SIGNATURE,
        'allowExpressDelivery'         => CarrierSettings::ALLOW_DELIVERY_TYPE_EXPRESS,
        'priceEveningDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_EVENING,
        'priceMorningDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_MORNING,
        'priceOnlyRecipient'           => CarrierSettings::PRICE_ONLY_RECIPIENT,
        'pricePackageTypeDigitalStamp' => CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
        'pricePackageTypeMailbox'      => CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
        'pricePackageTypePackageSmall' => CarrierSettings::PRICE_PACKAGE_TYPE_PACKAGE_SMALL,
        'pricePickup'                  => CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP,
        'priceSameDayDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY,
        'priceSignature'               => CarrierSettings::PRICE_SIGNATURE,
        'priceStandardDelivery'        => CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD,
        'priceCollect'                 => CarrierSettings::PRICE_COLLECT,
        'priceExpressDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_EXPRESS,
        'excludeParcelLockers'         => CheckoutSettings::EXCLUDE_PARCEL_LOCKERS,
    ];

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface
     */
    private $countryService;

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface
     */
    private $currencyService;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface
     */
    private $dropOffService;

    /**
     * @var \MyParcelNL\Pdk\Validation\Repository\SchemaRepository
     */
    private $schemaRepository;

    /**
     * @var \MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface
     */
    private $taxService;

    /**
     * @var \MyParcelNL\Pdk\Types\Service\TriStateService
     */
    private $triStateService;

    /**
     * @var \MyParcelNL\Pdk\Frontend\Contract\FrontendDataAdapterInterface
     */
    private $frontendDataAdapter;

    /**
     * @param  \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface     $countryService
     * @param  \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface    $currencyService
     * @param  \MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface $dropOffService
     * @param  \MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface      $taxService
     * @param  \MyParcelNL\Pdk\Validation\Repository\SchemaRepository    $schemaRepository
     * @param  \MyParcelNL\Pdk\Types\Service\TriStateService             $triStateService
     * @param  \MyParcelNL\Pdk\Frontend\Contract\FrontendDataAdapterInterface $frontendDataAdapter
     */
    public function __construct(
        CountryServiceInterface     $countryService,
        CurrencyServiceInterface    $currencyService,
        DropOffServiceInterface     $dropOffService,
        TaxServiceInterface         $taxService,
        SchemaRepository            $schemaRepository,
        TriStateService             $triStateService,
        FrontendDataAdapterInterface $frontendDataAdapter
    ) {
        $this->countryService      = $countryService;
        $this->currencyService     = $currencyService;
        $this->dropOffService      = $dropOffService;
        $this->taxService          = $taxService;
        $this->schemaRepository    = $schemaRepository;
        $this->triStateService     = $triStateService;
        $this->frontendDataAdapter = $frontendDataAdapter;
    }

    /**
     * Creates a carrier configuration for the delivery options.
     * Note that some of these properties will be deprecated and should be replaced by interactions with the capabilities API.
     * (e.g. packageTypes, features, shipmentOptionsPerPackageType)
     * @return array
     * @throws InvalidArgumentException
     */
    public function createPropositionConfig(): array
    {
        $config = ['carriers' => []];
        $carriers = Pdk::get(PropositionService::class)
            ->getCarriers(true);

        /**
         * @var FrontendDataAdapterInterface $adapter
         */
        $adapter = Pdk::get(FrontendDataAdapterInterface::class);

        // @TODO: this data should be based on calls by the DO to the capabilities API through a proxy in the PDK
        $config['carriers'] = array_map(function ($carrier) use ($adapter) {
            $legacyCarrier = $adapter->convertCarrierToLegacyFormat($carrier);

            return array_filter([
                "name"      => $legacyCarrier->name,
                "active"    => true,
                "subscription" => TriStateService::INHERIT, // This does not seem to be actually used in the DO?
                "packageTypes" => $legacyCarrier->capabilities->packageTypes,
                'deliveryTypes' => $legacyCarrier->capabilities->deliveryTypes,
                'deliveryCountries' => $carrier->outboundFeatures->deliveryCountries ?? [],
                "pickupCountries" => $carrier->outboundFeatures->pickupCountries ?? [],
                // smallPackagePickupCountries currently always equal deliveryCountries. If that changes before the capabilities endpoint is integrated, add it to the Proposition config.
                "smallPackagePickupCountries" => in_array(DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME, $legacyCarrier->capabilities->packageTypes) ? ($carrier->outboundFeatures->deliveryCountries ?? []) : [],
                "fakeDelivery" => $carrier->deliveryOptions['allowFakeDelivery'] ?? false,
                // Map shipment options to package type package as a fallback, if the proposition config does not have a "shipmentOptionsPerPackageType" property within the deliveryOptions.
                "shipmentOptionsPerPackageType" =>
                    array_key_exists('shipmentOptionsPerPackageType', $carrier->deliveryOptions) ?
                        $carrier->deliveryOptions['shipmentOptionsPerPackageType'] :
                        [
                            DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME => array_map(function ($key) {
                                return Str::snake($key);
                            }, array_keys((array) $legacyCarrier->capabilities->shipmentOptions)) ?? []
                        ],
                "features" => $carrier->deliveryOptions['availableFeatures'] ?? null,
                "addressFields" => $carrier->deliveryOptions['addressFields'] ?? null,
                "unsupportedParameters" => $carrier->deliveryOptions['unsupportedParameters'] ?? null
            ]);
        }, $carriers->all());
        return $config;
    }

    /**
     * Create the delivery options config including all carrier-specific feature toggles based on the cart.
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return array
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

        foreach ($carriers as $carrier) {
            // Use the legacy identifier for the delivery options, as that endpoint does not yet support the new identifiers.
            $identifier = FrontendData::getLegacyIdentifier($carrier->externalIdentifier);
            $settings['carrierSettings'][$identifier] = $this->createCarrierSettings($carrier, $cart, $packageType);
        }

        return $settings;
    }

    /**
     * Create the settings for a specific carrier based on the cart.
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier  $carrier
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return array
     */
    private function createCarrierSettings(Carrier $carrier, PdkCart $cart, string $packageType): array
    {
        $carrierSettings = CarrierSettings::fromCarrier($carrier);

        $dropOff           = $this->dropOffService->getForDate($carrierSettings);
        $dropOffCollection = $this->dropOffService->getPossibleDropOffDays($carrierSettings);
        $dropOffDays       = (new Collection($dropOffCollection))
            ->pluck('weekday')
            ->toArray();

        // Always use Europe/Amsterdam timezone for cutoff checks, because cutoff times are meant as local shop time.
        // This prevents bugs when the server runs in a different timezone (e.g. UTC).
        $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Amsterdam'));

        $minimumDropOffDelay = -1 === $cart->shippingMethod->minimumDropOffDelay
            ? $carrierSettings['dropOffDelay']
            : $cart->shippingMethod->minimumDropOffDelay;

        $cc = $cart->shippingMethod->shippingAddress->cc ?? null;
        if (
            $cc
            && $this->shouldUseInternationalMailboxPrice($packageType, $cc)) {
            $carrierSettings->pricePackageTypeMailbox = $carrierSettings->priceInternationalMailbox;
        }

        $settings = $this->getBaseSettings($carrierSettings, $cart);

        return array_merge(
            $settings,
            [
                'deliveryDaysWindow'   => $carrierSettings->deliveryDaysWindow,
                'dropOffDelay'         => max($minimumDropOffDelay, $carrierSettings->dropOffDelay),
                'allowSameDayDelivery' => ($settings['allowSameDayDelivery'] ?? false)
                    && 0 === $minimumDropOffDelay
                    && $now->format('H:i') <= ($carrierSettings['cutoffTimeSameDay'] ?? '00:00'),
                'cutoffTime'           => $dropOff->cutoffTime ?? null,
                'cutoffTimeSameDay'    => $carrierSettings['cutoffTimeSameDay'] ?? null,
                'dropOffDays'          => $dropOffDays,
            ]
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\CarrierSettings $carrierSettings
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart         $cart
     *
     * @return array
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

                // For pickup price, ensure it doesn't exceed shipping costs
                if ($key === CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP) {
                    $shippingCost = $this->currencyService->convertToEuros($cart->shipmentPrice);
                    $subtotal     = max(-$shippingCost, $value);
                }

                return $this->taxService->getShippingDisplayPrice((float) $subtotal);
            }

            return $value;
        }, self::CONFIG_CARRIER_SETTINGS_MAP);
    }

    /**
     * Filters all carrier options by the package type and weight.
     *
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return array{0: string, 1: \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection}
     */
    private function getValidCarrierOptions(PdkCart $cart): array
    {
        $allCarriers     = $this->frontendDataAdapter->carrierCollectionToLegacyFormat(
            AccountSettings::getCarriers()
        );
        $carrierSettings = Settings::get(CarrierSettings::ID);

        // Get the package types from the cart
        $cartPackageTypes = $cart->lines->pluck('product.settings.packageType');

        // Convert TriState::INHERIT to the default package type.
        $cartPackageTypes = $cartPackageTypes->map(
            function ($packageType) {
                if ($this->triStateService->cast($packageType) === TriStateService::INHERIT) {
                    return DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME;
                }

                return $packageType;
            }
        );

        // Get the largest package type first.
        // This will ensure we do not show delivery options with a smaller package type than fits what is in the cart.
        foreach ($cart->shippingMethod->allowedPackageTypes->sortBySize() as $packageType) {
            // Skip package types that do not match any of the items in the cart
            if (! $cartPackageTypes->contains($packageType->name)) {
                continue;
            }

            $weight = Pdk::get(WeightServiceInterface::class)
                ->addEmptyPackageWeight($cart->lines->getTotalWeight(), $packageType);

            $filteredCarriers = $allCarriers
                ->filter(
                    function (Carrier $carrier) use ($cart, $weight, $packageType, $carrierSettings): bool {
                        $hasDeliveryOptions =
                            $carrierSettings[$carrier->externalIdentifier][CarrierSettings::DELIVERY_OPTIONS_ENABLED] ?? false;

                        if (! $hasDeliveryOptions) {
                            return false;
                        }

                        $schema = $this->schemaRepository->getOrderValidationSchema(
                            $carrier->name,
                            $cart->shippingMethod->shippingAddress->cc,
                            // TODO: support full package type class instead of string
                            $packageType->name
                        );

                        $packageTypeValidation = $this->schemaRepository->validateOption(
                            $schema,
                            'properties.deliveryOptions.properties.packageType',
                            $packageType->name
                        );

                        $weightValidation = $this->schemaRepository->validateOption(
                            $schema,
                            'properties.physicalProperties.properties.weight',
                            $weight
                        );

                        return $packageTypeValidation && $weightValidation;
                    }
                );

            if ($filteredCarriers->isNotEmpty()) {
                return [$packageType->name, $filteredCarriers];
            }
        }

        return [DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME, $allCarriers];
    }

    /**
     * @param  string $packageType
     * @param  string $cc
     *
     * @return bool
     */
    private function shouldUseInternationalMailboxPrice(string $packageType, string $cc): bool
    {
        $isMailbox  = $packageType === DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME;
        $isNotLocal = ! $this->countryService->isLocalCountry($cc);

        return $isMailbox && $isNotLocal;
    }
}
