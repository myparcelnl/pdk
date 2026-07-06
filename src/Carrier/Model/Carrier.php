<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Base\Model\SdkBackedModel;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;

/**
 * Instantiate a Carrier model based on existing known data when passed an ID/Name, or creates a new Carrier model based on the data passed to the constructor.
 * If nothing is passed, the configured default carrier is returned.
 *
 * This Carrier model is modelled on top of the carrier as returned by the shipments/capabilities endpoint with additional metadata.
 * This gives us the relevant information about the carrier that we need to use, where the other API endpoints only concern themselves with being passed the ID/Name of the carrier.
 *
 *
 * Properties from the backing RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2 SDK model
 * @property string $carrier          Carrier name in CONSTANT_CASE from contract definitions
 * @property string[]|null  $packageTypes     Available package types as an array of CONSTANT_CASE strings from contract definitions
 * @property \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2|null  $options          Available shipment options including default/required states and additional metadata for options (e.g. insurance suboptions and their constraints)
 * @property string[]|null  $deliveryTypes    Available delivery types as an array of CONSTANT_CASE strings from contract definitions
 * @property string[]|null  $transactionTypes Available transaction types as an array of CONSTANT_CASE strings from contract definitions
 * @property \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCollo|null  $collo            Collo constraints
 */
class Carrier extends SdkBackedModel
{
    /**
     * Transient contract ID from capabilities response. Not persisted.
     *
     * @var int|null
     */
    public $contractId;

    /*
     * Inherit all getters and setters from this model.
     */
    protected $sdkModelClass = RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2::class;

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::POSTNL
     */
    public const CARRIER_POSTNL_LEGACY_NAME          = 'postnl';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::BPOST
     */
    public const CARRIER_BPOST_LEGACY_NAME           = 'bpost';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::CHEAP_CARGO
     */
    public const CARRIER_CHEAP_CARGO_LEGACY_NAME     = 'cheapcargo';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::DPD
     */
    public const CARRIER_DPD_LEGACY_NAME             = 'dpd';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU
     */
    public const CARRIER_DHL_FOR_YOU_LEGACY_NAME     = 'dhlforyou';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::DHL_PARCEL_CONNECT
     */
    public const CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME = 'dhlparcelconnect';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS
     */
    public const CARRIER_DHL_EUROPLUS_LEGACY_NAME    = 'dhleuroplus';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::UPS_STANDARD
     */
    public const CARRIER_UPS_STANDARD_LEGACY_NAME = 'upsstandard';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::UPS_EXPRESS_SAVER
     */
    public const CARRIER_UPS_EXPRESS_SAVER_LEGACY_NAME = 'upsexpresssaver';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::GLS
     */
    public const CARRIER_GLS_LEGACY_NAME       = 'gls';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::BRT
     */
    public const CARRIER_BRT_LEGACY_NAME       = 'brt';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::TRUNKRS
     */
    public const CARRIER_TRUNKRS_LEGACY_NAME   = 'trunkrs';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::INPOST
     */
    public const CARRIER_INPOST_LEGACY_NAME    = 'inpost';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::POSTE_ITALIANE
     */
    public const CARRIER_POSTE_ITALIANE_LEGACY_NAME = 'posteitaliane';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::SPRING
     */
    public const CARRIER_SPRING_LEGACY_NAME    = 'spring';

    /**
     * @deprecated use RefCapabilitiesSharedCarrierV2::VIA_TIM
     */
    public const CARRIER_VIA_TIM_LEGACY_NAME   = 'viatim';

    /**
     * Legacy names as used by delivery options and internal storage.
     *
     * @deprecated use new carrier names directly
     */
    public const CARRIER_NAME_TO_LEGACY_MAP = [
        RefCapabilitiesSharedCarrierV2::BPOST              => self::CARRIER_BPOST_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::CHEAP_CARGO        => self::CARRIER_CHEAP_CARGO_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS       => self::CARRIER_DHL_EUROPLUS_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU        => self::CARRIER_DHL_FOR_YOU_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::DHL_PARCEL_CONNECT => self::CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::DPD                => self::CARRIER_DPD_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::POSTNL             => self::CARRIER_POSTNL_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::UPS_STANDARD       => self::CARRIER_UPS_STANDARD_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::UPS_EXPRESS_SAVER  => self::CARRIER_UPS_EXPRESS_SAVER_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::GLS                => self::CARRIER_GLS_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::BRT                => self::CARRIER_BRT_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::TRUNKRS            => self::CARRIER_TRUNKRS_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::INPOST             => self::CARRIER_INPOST_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::POSTE_ITALIANE     => self::CARRIER_POSTE_ITALIANE_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::SPRING             => self::CARRIER_SPRING_LEGACY_NAME,
        RefCapabilitiesSharedCarrierV2::VIA_TIM            => self::CARRIER_VIA_TIM_LEGACY_NAME,
    ];

    /**
     * Names to ids
     * @deprecated use mapping functionality from the SDK when available (INT-1441)
     */
    public const CARRIER_NAME_ID_MAP = [
        RefCapabilitiesSharedCarrierV2::BPOST              => RefTypesCarrier::BPOST,
        RefCapabilitiesSharedCarrierV2::CHEAP_CARGO        => RefTypesCarrier::CHEAP_CARGO,
        RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS       => RefTypesCarrier::DHL_EUROPLUS,
        RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU        => RefTypesCarrier::DHL_FOR_YOU,
        RefCapabilitiesSharedCarrierV2::DHL_PARCEL_CONNECT => RefTypesCarrier::DHL_PARCEL_CONNECT,
        RefCapabilitiesSharedCarrierV2::DPD                => RefTypesCarrier::DPD,
        RefCapabilitiesSharedCarrierV2::POSTNL             => RefTypesCarrier::POSTNL,
        RefCapabilitiesSharedCarrierV2::GLS                => RefTypesCarrier::GLS,
        RefCapabilitiesSharedCarrierV2::UPS_STANDARD       => RefTypesCarrier::UPS_STANDARD,
        RefCapabilitiesSharedCarrierV2::UPS_EXPRESS_SAVER  => RefTypesCarrier::UPS_EXPRESS_SAVER,
        RefCapabilitiesSharedCarrierV2::BRT                => RefTypesCarrier::BRT,
        RefCapabilitiesSharedCarrierV2::TRUNKRS            => RefTypesCarrier::TRUNKRS,
        RefCapabilitiesSharedCarrierV2::INPOST             => RefTypesCarrier::INPOST,
        RefCapabilitiesSharedCarrierV2::POSTE_ITALIANE     => RefTypesCarrier::POSTE_ITALIANE,
        RefCapabilitiesSharedCarrierV2::SPRING             => RefTypesCarrier::SPRING,
        RefCapabilitiesSharedCarrierV2::VIA_TIM            => RefTypesCarrier::VIA_TIM,
    ];

    /**
     * Whether a carrier name is supported by this PDK version.
     *
     * Carriers absent from this set are filtered out at the boundary
     * ({@see \MyParcelNL\Pdk\Account\Service\AccountSettingsService::getCarriers},
     * {@see \MyParcelNL\Pdk\App\Action\Capabilities\CapabilitiesAction}'s response)
     * so a server-side proposition update introducing a new carrier cannot expose
     * it to admin or checkout, which would otherwise lead to encode-side throws
     * during export.
     *
     * Currently backed by {@see self::CARRIER_NAME_ID_MAP}; switch to an
     * SDK-provided definition when INT-1441 lands so call sites stay unchanged.
     */
    public static function isSupported(string $carrierName): bool
    {
        return array_key_exists($carrierName, self::CARRIER_NAME_ID_MAP);
    }

    /**
     * Translate a numeric carrier id (as exposed by external APIs in legacy CoreAPI shape)
     * to its V2 carrier name (e.g. 1 → "POSTNL", 15 → "BRT"). Returns null when the id is
     * not in the local id↔name mapping — typically a carrier that exists in the API enum
     * but is not yet known to this PDK version.
     *
     * Pure static-map lookup with no shop/repository dependency, so it is safe to call
     * before any carrier collection has been resolved or persisted.
     *
     * Currently backed by {@see self::CARRIER_NAME_ID_MAP}; switch to an
     * SDK-provided definition when INT-1441 lands so call sites stay unchanged.
     *
     * @param  int $id Numeric carrier id from a legacy CoreAPI payload.
     *
     * @return null|string V2 carrier name, or null when the id is not in the local mapping.
     */
    public static function v2NameFromLegacyId(int $id): ?string
    {
        return array_search($id, self::CARRIER_NAME_ID_MAP, true) ?: null;
    }

    /**
     * Any attributes here extend/overwrite the data from RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2.
     * @see RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2
     */
    protected $attributes = [];

    /**
     * Any attributes here extend/overwrite the getters from RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2.
     * @see RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2
     */
    protected $casts = [];

    /**
     * Create a new Carrier model instance with the provided data.
     *
     * To fetch existing carriers from account data, use CarrierRepository instead.
     *
     * @param  null|array $data
     * @see \MyParcelNL\Pdk\Carrier\Repository\CarrierRepository
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data ?? []);
    }

    /**
     * Cached allowlist of registered capabilities keys, shared across all Carrier instances.
     *
     * @var null|array<string, true>
     */
    private static $registeredCapabilitiesKeys;

    /**
     * Allowlist of camelCase option keys that have a registered OrderOptionDefinition in this PDK.
     *
     * Capabilities responses may carry options the PDK has no calculator/UI label for; those
     * are stripped at the SDK boundary so they never reach the admin or checkout. Keys are
     * camelCase to match the SDK options model's attributeMap. Cached because definitions
     * don't change at runtime.
     *
     * @return array<string, true>
     */
    public static function getRegisteredCapabilitiesKeys(): array
    {
        if (self::$registeredCapabilitiesKeys === null) {
            /** @var OrderOptionDefinitionInterface[] $definitions */
            $definitions = Pdk::get('orderOptionDefinitions');

            self::$registeredCapabilitiesKeys = [];

            foreach ($definitions as $definition) {
                $key = $definition->getCapabilitiesOptionsKey();

                if ($key !== null) {
                    self::$registeredCapabilitiesKeys[$key] = true;
                }
            }
        }

        return self::$registeredCapabilitiesKeys;
    }

    /**
     * Utility helper to directly get the option definition for a shipment option by its capabilities key.
     * Avoids having to chain through multiple levels of getters and null checks to get to the same data, as this is a common action when working with carriers and their options.
     *
     * @param  string $capabilitiesKey camelCase key, e.g. 'requiresSignature'
     *
     * @return null|\MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedOptionsBaseOptionV2
     */
    public function getOptionMetadata(string $capabilitiesKey)
    {
        if (! $this->options) {
            return null;
        }

        $getter = 'get' . ucfirst($capabilitiesKey);

        if (! method_exists($this->options, $getter)) {
            return null;
        }

        $option = $this->options->$getter();

        if (! $option || ! method_exists($option, 'getIsRequired')) {
            return null;
        }

        // Return type only type-hinted in comments, as the actual return type is a union of SDK types which is not supported in PHP 7.4
        return $option;
    }
}
