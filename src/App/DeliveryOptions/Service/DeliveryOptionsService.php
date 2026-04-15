<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use DateTimeImmutable;
use DateTimeZone;
use MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Support\Str;

class DeliveryOptionsService implements DeliveryOptionsServiceInterface
{
    /**
     * Settings map for non-shipment-option entries (delivery types, package types, etc.)
     * that are not covered by OrderOptionDefinitions. Shipment option allow/price keys
     * are built dynamically from definitions in getCarrierSettingsMap().
     */
    private const NON_DEFINITION_CARRIER_SETTINGS_MAP = [
        'allowDeliveryOptions'         => CarrierSettings::ALLOW_DELIVERY_OPTIONS,
        'allowStandardDelivery'        => CarrierSettings::ALLOW_STANDARD_DELIVERY,
        'allowEveningDelivery'         => CarrierSettings::ALLOW_EVENING_DELIVERY,
        'allowMondayDelivery'          => CarrierSettings::ALLOW_MONDAY_DELIVERY,
        'allowMorningDelivery'         => CarrierSettings::ALLOW_MORNING_DELIVERY,
        'allowPickupLocations'         => CarrierSettings::ALLOW_PICKUP_DELIVERY,
        'allowExpressDelivery'         => CarrierSettings::ALLOW_DELIVERY_TYPE_EXPRESS,
        'priceEveningDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_EVENING_DELIVERY,
        'priceMorningDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_MORNING_DELIVERY,
        'pricePackageTypeDigitalStamp' => CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
        'pricePackageTypeMailbox'      => CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
        'pricePackageTypePackageSmall' => CarrierSettings::PRICE_PACKAGE_TYPE_PACKAGE_SMALL,
        'pricePickup'                  => CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP,
        'priceSameDayDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY_DELIVERY,
        'priceStandardDelivery'        => CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD_DELIVERY,
        'priceExpressDelivery'         => CarrierSettings::PRICE_DELIVERY_TYPE_EXPRESS_DELIVERY,
        'excludeParcelLockers'         => CheckoutSettings::EXCLUDE_PARCEL_LOCKERS,
    ];

    /**
     * @var \MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface
     */
    private $cartCalculationService;

    /**
     * @var \MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService
     */
    private $capabilitiesValidation;

    /**
     * @var \MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface
     */
    private $carrierRepository;

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
     * @var \MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface
     */
    private $taxService;

    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface $cartCalculationService
     * @param  \MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService     $capabilitiesValidation
     * @param  \MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface       $carrierRepository
     * @param  \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface             $countryService
     * @param  \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface            $currencyService
     * @param  \MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface         $dropOffService
     * @param  \MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface              $taxService
     */
    public function __construct(
        CartCalculationServiceInterface  $cartCalculationService,
        CapabilitiesValidationService    $capabilitiesValidation,
        CarrierRepositoryInterface       $carrierRepository,
        CountryServiceInterface          $countryService,
        CurrencyServiceInterface         $currencyService,
        DropOffServiceInterface          $dropOffService,
        TaxServiceInterface              $taxService
    ) {
        $this->cartCalculationService  = $cartCalculationService;
        $this->capabilitiesValidation  = $capabilitiesValidation;
        $this->carrierRepository       = $carrierRepository;
        $this->countryService          = $countryService;
        $this->currencyService         = $currencyService;
        $this->dropOffService          = $dropOffService;
        $this->taxService              = $taxService;
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
            $identifier = FrontendData::getLegacyCarrierIdentifier($carrier->carrier);
            $settings['carrierSettings'][$identifier] = array_merge(
                $this->createCarrierSettings($carrier, $cart, $packageType),
                ['contractId' => $carrier->contractId ?? null]
            );
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
            && $this->shouldUseInternationalMailboxPrice($packageType, $cc)
        ) {
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
        }, self::getCarrierSettingsMap());
    }

    /**
     * Find the best package type for this cart and the carriers that support it.
     *
     * Uses a tiered approach:
     * 1. Carrier model (contract definitions) → which package types each carrier supports
     * 2. Broad capabilities call (per country) → carrier-level weight range for early bail-out
     * 3. Per-package-type capabilities call → accurate weight limits for the specific package type
     *
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return array{0: string, 1: \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection}
     */
    private function getValidCarrierOptions(PdkCart $cart): array
    {
        $allCarriers           = $this->carrierRepository->all();
        $carrierSettings       = Settings::get(CarrierSettings::ID);
        $cc                    = $cart->shippingMethod->shippingAddress->cc ?? null;
        $candidatePackageTypes = $this->getCandidatePackageTypes($cart);

        foreach ($candidatePackageTypes as $packageTypeName => $v2PackageType) {
            $weight           = $this->cartCalculationService->getCartWeightForPackageType($cart, $packageTypeName);
            $filteredCarriers = $this->filterCarriersForPackageType(
                $allCarriers,
                $carrierSettings,
                $cc,
                $v2PackageType,
                $weight
            );

            if ($filteredCarriers->isNotEmpty()) {
                return [$packageTypeName, $filteredCarriers];
            }
        }

        return [DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME, $allCarriers];
    }

    /**
     * Determine which package types to try, starting from the cart's desired type and
     * upgrading to larger types if the total order weight doesn't fit.
     *
     * Uses a "next fitting size" approach: if the desired type (e.g. mailbox) can't
     * accommodate the total order weight, tries the next larger type from the shipping
     * method's allowed list (e.g. small_package → package).
     *
     * Package type ordering is determined by capabilities weight limits — the type with
     * the highest max weight across carriers is considered the "largest".
     *
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return array<string, string> PDK package type name => V2 package type name
     */
    private function getCandidatePackageTypes(PdkCart $cart): array
    {
        $cc            = $cart->shippingMethod->shippingAddress->cc ?? null;
        $allowedTypes  = $this->getAvailablePackageTypes($cart);
        $cartTypes     = $this->cartCalculationService->getCartPackageTypes($cart);

        // Fetch capabilities for each allowed type to determine weight-based ordering.
        // Cached per cc+packageType — subsequent use in filterCarriersForPackageType hits cache.
        $typeWeights = $cc
            ? $this->capabilitiesValidation->getPackageTypeWeights($cc, $allowedTypes)
            : [];

        // The desired type is the heaviest type among the cart's product types.
        $desiredType   = $this->capabilitiesValidation->resolveHeaviestType($cartTypes, $typeWeights);
        $desiredWeight = $typeWeights[$desiredType] ?? null;

        $candidates = [];

        foreach ($allowedTypes as $packageTypeName => $v2PackageType) {
            $typeWeight = $typeWeights[$packageTypeName] ?? null;

            // Only include the desired type and types that are heavier (upgrade path).
            if ($packageTypeName !== $desiredType && Utils::compareNullableInts($typeWeight, $desiredWeight) < 0) {
                continue;
            }

            // Mailbox requires the cart contents to physically fit (product-level, not carrier-level).
            if (DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME === $packageTypeName
                && $this->cartCalculationService->calculateMailboxPercentage($cart) > 100.0
            ) {
                continue;
            }

            $candidates[$packageTypeName] = $v2PackageType;
        }

        // Sort: desired type first, then ascending by weight capacity (smallest upgrade first).
        uksort($candidates, static function (string $a, string $b) use ($typeWeights, $desiredType): int {
            if ($a === $desiredType) {
                return -1;
            }
            if ($b === $desiredType) {
                return 1;
            }

            return Utils::compareNullableInts($typeWeights[$a] ?? null, $typeWeights[$b] ?? null);
        });

        return $candidates;
    }

    /**
     * Get available package types as a PDK name => V2 name map.
     *
     * Reads from the shipping method's allowedPackageTypes, which is resolved from
     * checkout settings by the PdkShippingMethod attribute getter.
     *
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return array<string, string> PDK package type name => V2 package type name
     */
    private function getAvailablePackageTypes(PdkCart $cart): array
    {
        $available = [];

        foreach ($cart->shippingMethod->allowedPackageTypes as $packageType) {
            $name   = is_object($packageType) ? $packageType->name : $packageType;
            $v2Type = DeliveryOptions::PACKAGE_TYPES_V2_MAP[$name] ?? null;

            if ($v2Type) {
                $available[$name] = $v2Type;
            }
        }

        return $available;
    }

    /**
     * Filter carriers that are enabled and support the given package type and weight.
     *
     * Uses per-package-type capabilities call for accurate weight limits. Sets the
     * contract ID from the response on each matching carrier for downstream propagation.
     *
     * @param  \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection $allCarriers
     * @param  array                                                 $carrierSettings
     * @param  null|string                                           $cc
     * @param  string                                                $v2PackageType
     * @param  int                                                   $weight
     *
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    private function filterCarriersForPackageType(
        $allCarriers,
        array $carrierSettings,
        ?string $cc,
        string $v2PackageType,
        int $weight
    ) {
        // Fetch capabilities for this specific country + package type combination.
        // Cached per cc+packageType — accurate weight limits per package type.
        $capabilitiesByCarrier = $cc
            ? $this->capabilitiesValidation->getCapabilitiesForPackageType($cc, $v2PackageType)
            : [];

        return $allCarriers->filter(
            function (Carrier $carrier) use ($carrierSettings, $capabilitiesByCarrier, $weight): bool {
                if (! $this->isCarrierEnabled($carrierSettings, $carrier)) {
                    return false;
                }

                // Without capabilities (no recipient country), accept all enabled carriers.
                if (empty($capabilitiesByCarrier)) {
                    return true;
                }

                $capability = $capabilitiesByCarrier[$carrier->carrier] ?? null;

                // Carrier not in capabilities response → not available for this destination + package type.
                if (! $capability) {
                    return false;
                }

                if (! $this->capabilitiesValidation->capabilitySupportsWeight($capability, $weight)) {
                    return false;
                }

                $contract = $capability->getContract();

                if ($contract) {
                    $carrier->contractId = $contract->getId();
                }

                return true;
            }
        );
    }

    /**
     * @param  array                                  $carrierSettings
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return bool
     */
    private function isCarrierEnabled(array $carrierSettings, Carrier $carrier): bool
    {
        return $carrierSettings[$carrier->carrier][CarrierSettings::DELIVERY_OPTIONS_ENABLED] ?? false;
    }

    /**
     * Build the full carrier settings map by merging definition-derived allow/price keys
     * with the static non-definition entries.
     *
     * @return array<string, string>
     */
    private static function getCarrierSettingsMap(): array
    {
        /** @var \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');
        $map         = [];

        foreach ($definitions as $definition) {
            $allowKey = $definition->getAllowSettingsKey();

            if ($allowKey) {
                $map[$allowKey] = $allowKey;
            }

            $priceKey = $definition->getPriceSettingsKey();

            if ($priceKey) {
                $map[$priceKey] = $priceKey;
            }
        }

        return array_merge($map, self::NON_DEFINITION_CARRIER_SETTINGS_MAP);
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
