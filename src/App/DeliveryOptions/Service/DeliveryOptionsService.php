<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Service;

use DateTimeImmutable;
use DateTimeZone;
use MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\SettingKey;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\PackageType;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;
use MyParcelNL\Sdk\Support\Str;
use Throwable;

class DeliveryOptionsService implements DeliveryOptionsServiceInterface
{
    private const PICKUP_CAPABILITIES_TIMEOUT = 2.0;

    private const PICKUP_CAPABILITIES_CONNECT_TIMEOUT = 1.0;

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

        [$packageType, $carriers, $knownWeight] = $this->getValidCarrierOptions($cart);

        $pickupUnavailableByCarrier = $this->getWeightSpecificPickupRestrictions(
            $cart,
            $packageType,
            $carriers,
            $knownWeight
        );

        $showPriceSurcharge =
            Settings::get(CheckoutSettings::PRICE_TYPE, CheckoutSettings::ID) === CheckoutSettings::PRICE_TYPE_INCLUDED;

        $settings = [
            'packageType'           => $packageType,
            'carrierSettings'       => [],
            'basePrice'             => $this->currencyService->convertToEuros($cart->shipmentPrice),
            'priceStandardDelivery' => $showPriceSurcharge ? $cart->shipmentPrice : 0,
        ];

        foreach ($carriers as $carrier) {
            if (null === $carrier->carrier) {
                continue;
            }
            // Use the legacy identifier for the delivery options, as that endpoint does not yet support the new identifiers.
            $identifier = FrontendData::getLegacyCarrierIdentifier($carrier->carrier);
            $settings['carrierSettings'][$identifier] = array_merge(
                $this->createCarrierSettings(
                    $carrier,
                    $cart,
                    $packageType,
                    isset($pickupUnavailableByCarrier[$carrier->carrier])
                ),
                ['contractId' => $carrier->contractId ?? null]
            );
        }

        return $settings;
    }

    /**
     * Create the settings for a specific carrier based on the cart.
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier  $carrier
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     * @param  string                                    $packageType
     * @param  bool                                      $isPickupUnavailable
     *
     * @return array
     */
    private function createCarrierSettings(
        Carrier $carrier,
        PdkCart $cart,
        string $packageType,
        bool $isPickupUnavailable
    ): array {
        $carrierSettings = CarrierSettings::fromCarrier($carrier);

        $dropOff           = $this->dropOffService->getForDate($carrierSettings);
        $dropOffCollection = $this->dropOffService->getPossibleDropOffDays($carrierSettings);
        $dropOffDays       = (new Collection($dropOffCollection))
            ->pluck('weekday')
            ->toArray();

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

        if ($isPickupUnavailable) {
            $settings[SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP)] = false;
        }

        return array_merge(
            $settings,
            [
                'deliveryDaysWindow'   => $carrierSettings->deliveryDaysWindow,
                'dropOffDelay'         => max($minimumDropOffDelay, $carrierSettings->dropOffDelay),
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
                if ($key === SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::PICKUP)) {
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
     * @return array{0: string, 1: \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection, 2: null|int}
     */
    private function getValidCarrierOptions(PdkCart $cart): array
    {
        $carrierSettings = Settings::get(CarrierSettings::ID);

        $carrierSettings = array_filter(
            is_array($carrierSettings) ? $carrierSettings : [],
            static fn($settings): bool => is_array($settings)
        );

        if (empty($carrierSettings)) {
            return [DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME, new CarrierCollection(), null];
        }

        $allCarriers           = $this->carrierRepository->all();
        $shippingAddress       = $cart->shippingMethod->shippingAddress;
        $cc                    = $shippingAddress->cc ?? null;
        $isBusiness            = $shippingAddress->isBusiness;
        $candidatePackageTypes = $this->getCandidatePackageTypes($cart);

        foreach ($candidatePackageTypes as $packageTypeName => $v2PackageType) {
            $weight           = $this->cartCalculationService->getCartWeightForPackageType($cart, $packageTypeName);
            $filteredCarriers = $this->filterCarriersForPackageType(
                $allCarriers,
                $carrierSettings,
                $cc,
                $v2PackageType,
                $weight,
                $isBusiness
            );

            if ($filteredCarriers->isNotEmpty()) {
                return [
                    $packageTypeName,
                    $filteredCarriers,
                    $this->getKnownCartWeight($cart, $packageTypeName),
                ];
            }
        }

        return [DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME, $allCarriers, null];
    }

    /**
     * Resolve an exact cart weight only when every deliverable line has a positive product weight.
     *
     * Product weight zero is indistinguishable from "not configured" in the shared model. Treating a
     * partially known sum as exact can hide valid checkout options. Empty-package weight is therefore
     * added only after all product weights are known; packaging weight alone does not make the total
     * known. The nullable result also keeps a real one-gram product distinct from the API-safe one-gram
     * placeholder used elsewhere for an unknown zero total.
     *
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     * @param  string                                  $packageTypeName
     *
     * @return null|int Exact weight in grams, including configured empty-package weight
     */
    private function getKnownCartWeight(PdkCart $cart, string $packageTypeName): ?int
    {
        $deliverableLines = $cart->lines
            ->onlyDeliverable()
            ->filter(static function (PdkOrderLine $line): bool {
                return $line->quantity > 0;
            });

        if (
            $deliverableLines->isEmpty()
            || ! $deliverableLines->every(static function (PdkOrderLine $line): bool {
                return null !== $line->product && $line->product->weight > 0;
            })
        ) {
            return null;
        }

        return Pdk::get(WeightServiceInterface::class)->addEmptyPackageWeight(
            $deliverableLines->getTotalWeight(),
            new PackageType([
                'name' => $packageTypeName,
                'id'   => DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP[$packageTypeName] ?? null,
            ])
        );
    }

    /**
     * Fetch weight-aware delivery types after the existing package/carrier selection has completed.
     *
     * This is an optional checkout refinement. The established unweighted lookup remains the source
     * for package selection, carrier filtering and contract selection. Consequently this method makes
     * at most one extra request, only for merchants who enabled pickup. Any request failure, ambiguous
     * contract or incomplete response fails open and leaves the configured checkout options unchanged.
     *
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart                   $cart
     * @param  string                                                    $packageTypeName
     * @param  \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection    $carriers
     * @param  null|int                                                  $knownWeight
     *
     * @return array<string, true> Carrier names for which pickup is proven unavailable at this weight
     */
    private function getWeightSpecificPickupRestrictions(
        PdkCart $cart,
        string $packageTypeName,
        CarrierCollection $carriers,
        ?int $knownWeight
    ): array {
        $shippingAddress = $cart->shippingMethod->shippingAddress;
        $cc              = $shippingAddress->cc ?? null;
        $v2PackageType   = DeliveryOptions::PACKAGE_TYPES_V2_MAP[$packageTypeName] ?? null;

        if (
            null === $knownWeight
            || ! $cc
            || ! $v2PackageType
            || $carriers->isEmpty()
            || ! $this->isPickupEnabledForAnyCarrier($carriers)
        ) {
            return [];
        }

        $baseRequest = [
            'recipient'    => [
                'country_code' => $cc,
                'is_business'  => $shippingAddress->isBusiness,
            ],
            'package_type' => $v2PackageType,
        ];

        try {
            // This exact unweighted request was already made during carrier selection and is served from cache.
            $unweightedCapabilities = $this->capabilitiesValidation->getRepository()->getCapabilities($baseRequest);
            $weightedCapabilities   = $this->capabilitiesValidation->getRepository()->getCapabilitiesWithTimeout(
                $baseRequest + ['physical_properties' => [
                    'weight' => [
                        'value' => $knownWeight,
                        'unit'  => WeightServiceInterface::UNIT_GRAMS,
                    ],
                ]],
                self::PICKUP_CAPABILITIES_TIMEOUT,
                self::PICKUP_CAPABILITIES_CONNECT_TIMEOUT
            );

            $pickupUnavailableByCarrier = [];

            foreach ($carriers as $carrier) {
                $unweightedCapability = $this->getMatchingCapability($unweightedCapabilities, $carrier);
                $weightedCapability   = $this->getMatchingCapability($weightedCapabilities, $carrier);

                if (! $unweightedCapability || ! $weightedCapability) {
                    continue;
                }

                /** @var mixed $unweightedDeliveryTypes Runtime responses can violate the generated SDK PHPDoc. */
                $unweightedDeliveryTypes = $unweightedCapability->getDeliveryTypes();
                /** @var mixed $weightedDeliveryTypes Runtime responses can violate the generated SDK PHPDoc. */
                $weightedDeliveryTypes = $weightedCapability->getDeliveryTypes();

                if (
                    ! $this->hasValidDeliveryTypes($unweightedDeliveryTypes)
                    || ! $this->hasValidDeliveryTypes($weightedDeliveryTypes)
                ) {
                    continue;
                }

                /** @var string[] $unweightedDeliveryTypes The generated SDK documents enum values as objects. */
                /** @var string[] $weightedDeliveryTypes The generated SDK documents enum values as objects. */

                // Only a pickup-to-no-pickup delta proves that the weight caused the restriction.
                if (! in_array(RefTypesDeliveryTypeV2::PICKUP, $unweightedDeliveryTypes, true)) {
                    continue;
                }

                if (! in_array(RefTypesDeliveryTypeV2::PICKUP, $weightedDeliveryTypes, true)) {
                    $pickupUnavailableByCarrier[$carrier->carrier] = true;
                }
            }

            return $pickupUnavailableByCarrier;
        } catch (Throwable $exception) {
            Logger::warning(
                'Could not resolve weight-specific delivery types; preserving configured checkout options',
                ['error' => $exception->getMessage()]
            );

            return [];
        }
    }

    /**
     * An empty list is valid and authoritative; any non-string or empty value makes the response ambiguous.
     *
     * @param  mixed $deliveryTypes
     */
    private function hasValidDeliveryTypes($deliveryTypes): bool
    {
        if (! is_array($deliveryTypes)) {
            return false;
        }

        foreach ($deliveryTypes as $deliveryType) {
            if (! is_string($deliveryType) || '' === $deliveryType) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  RefCapabilitiesResponseCapabilityV2[] $capabilities
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier  $carrier
     *
     * @return null|RefCapabilitiesResponseCapabilityV2
     */
    private function getMatchingCapability(array $capabilities, Carrier $carrier): ?RefCapabilitiesResponseCapabilityV2
    {
        $matches = array_values(array_filter(
            $capabilities,
            static function ($capability) use ($carrier): bool {
                /** @var mixed $capabilityCarrier Runtime value is a string despite the generated SDK PHPDoc. */
                $capabilityCarrier = $capability->getCarrier();

                if ($capabilityCarrier !== $carrier->carrier) {
                    return false;
                }

                if (! $carrier->contractId) {
                    return true;
                }

                $contract = $capability->getContract();

                return $contract && (int) $contract->getId() === (int) $carrier->contractId;
            }
        ));

        return 1 === count($matches) ? $matches[0] : null;
    }

    private function isPickupEnabledForAnyCarrier(CarrierCollection $carriers): bool
    {
        return $carriers->contains(static function (Carrier $carrier): bool {
            return CarrierSettings::fromCarrier($carrier)->allowPickupLocations;
        });
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
        $shippingAddress = $cart->shippingMethod->shippingAddress;
        $cc              = $shippingAddress->cc ?? null;
        $isBusiness      = $shippingAddress->isBusiness;
        $allowedTypes    = $this->getAvailablePackageTypes($cart);
        $cartTypes       = $this->cartCalculationService->getCartPackageTypes($cart);

        // Fetch capabilities for each allowed type to determine weight-based ordering.
        // Passing the recipient's business flag keeps the same cache key as
        // filterCarriersForPackageType, so that later per-type carrier lookup hits this cache.
        $typeWeights = $cc
            ? $this->capabilitiesValidation->getPackageTypeWeights($cc, $allowedTypes, true, $isBusiness)
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
            if (
                DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME === $packageTypeName
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
     * @param  bool                                                  $isBusiness Whether the checkout recipient is a business.
     *
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    private function filterCarriersForPackageType(
        $allCarriers,
        array $carrierSettings,
        ?string $cc,
        string $v2PackageType,
        int $weight,
        bool $isBusiness
    ) {
        // Cache key: cc+package_type+isBusiness — shareable across orders with different weights.
        // The recipient is known at checkout, so the business flag is sent explicitly (never omitted).
        // Carrier presence and weight constraints are evaluated client-side per carrier.
        $capabilitiesByCarrier = $cc
            ? $this->capabilitiesValidation->indexByCarrier(
                $this->capabilitiesValidation->getRepository()->getCapabilities([
                    'recipient'    => ['country_code' => $cc, 'is_business' => $isBusiness],
                    'package_type' => $v2PackageType,
                ])
            )
            : [];

        return $allCarriers->filter(
            function (Carrier $carrier) use ($carrierSettings, $capabilitiesByCarrier, $weight, $cc, $v2PackageType): bool {
                if (! $this->isCarrierEnabled($carrierSettings, $carrier)) {
                    return false;
                }

                // Without recipient set, accept all enabled carriers.
                if (! $cc) {
                    return true;
                }

                if (
                    $v2PackageType === RefShipmentPackageTypeV2::MAILBOX
                    && ! $this->countryService->isLocalCountry($cc)
                    && ! ($carrierSettings[$carrier->carrier][SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_INTERNATIONAL_MAILBOX)] ?? false)
                ) {
                    return false;
                }

                $capability = $capabilitiesByCarrier[$carrier->carrier] ?? null;

                // Carrier not in capabilities response → not available for this destination + package type.
                if (! $capability) {
                    return false;
                }

                if (! $this->capabilitiesValidation->supportsWeight($capability, $weight)) {
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
     * Build the settings map exposed to the Delivery Options checkout widget.
     *
     * The delivery- and package-type lists below are hand-curated to match the
     * fields the widget currently understands — they are NOT yet driven from
     * the carrier's capabilities. Adding a new type means updating these lists
     * AND making the widget render the new field. When the widget becomes
     * fully capability-driven, this hand-curation collapses into iteration
     * over $carrier->deliveryTypes / packageTypes directly.
     *
     * @return array<string, string>
     */
    public static function getCarrierSettingsMap(): array
    {
        // Auto-derived from the SDK V2 enums, filtered to PDK-supported types
        // via DeliveryOptions::isDeliveryTypeSupported() / isPackageTypeSupported().
        // PDK-only consts (no SDK counterpart) appended explicitly.
        $supportedDeliveryTypes = array_values(array_filter(
            RefTypesDeliveryTypeV2::getAllowableEnumValues(),
            static function (string $v2): bool {
                return DeliveryOptions::isDeliveryTypeSupported($v2);
            }
        ));

        $allowDeliveryTypes = array_merge($supportedDeliveryTypes, [DeliveryOptions::DELIVERY_OPTION_MONDAY]);
        $priceDeliveryTypes = $supportedDeliveryTypes;

        // Default package type's price is the carrier's basePrice, not a
        // surcharge. Excluded to avoid stacking semantics.
        $pricePackageTypes = array_filter(
            RefShipmentPackageTypeV2::getAllowableEnumValues(),
            static function (string $v2): bool {
                return DeliveryOptions::isPackageTypeSupported($v2)
                    && $v2 !== DeliveryOptions::DEFAULT_PACKAGE_TYPE_V2;
            }
        );

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

        foreach ($allowDeliveryTypes as $type) {
            $key       = SettingKey::allow($type);
            $map[$key] = $key;
        }

        foreach ($priceDeliveryTypes as $type) {
            $map[SettingKey::price($type)] = SettingKey::priceDeliveryType($type);
        }

        foreach ($pricePackageTypes as $type) {
            $key       = SettingKey::pricePackageType($type);
            $map[$key] = $key;
        }

        // Special-case overrides — the widget exposes these under JS field
        // names that don't follow the formula:
        $map[SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_ALLOW_HOME)] = SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_ALLOW_HOME); // master toggle
        // Express is stored under the legacy 'allowDeliveryTypeExpress' attribute (via
        // SettingKey ALLOW_EXCEPTIONS) but exposed to the widget under the clean
        // JS field 'allowExpressDelivery'. Swap the loop's entry for the JS-clean key.
        $expressStorageKey = SettingKey::allow(RefTypesDeliveryTypeV2::EXPRESS);
        unset($map[$expressStorageKey]);
        $map['allowExpressDelivery'] = $expressStorageKey;
        // Pickup is exposed under the short JS field 'pricePickup' (not 'pricePickupDelivery'
        // produced by the auto-derive loop). Drop the loop's entry to avoid two JS fields
        // pointing at the same storage attribute.
        unset($map[SettingKey::price(RefTypesDeliveryTypeV2::PICKUP)]);
        $map['pricePickup'] = SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::PICKUP);
        $map['excludeParcelLockers'] = CheckoutSettings::EXCLUDE_PARCEL_LOCKERS; // different settings class

        return $map;
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
