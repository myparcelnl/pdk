<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\SdkBackedModel;
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
        RefCapabilitiesSharedCarrierV2::TRUNKRS            => RefTypesCarrier::TRUNKRS,
    ];

    /**
     * @deprecated use mapping functionality from the SDK when available
     * @see CARRIER_NAME_ID_MAP
     */
    public const CARRIER_LEGACY_NAME_ID_MAP = [
        self::CARRIER_BPOST_LEGACY_NAME      => RefTypesCarrier::BPOST,
        self::CARRIER_CHEAP_CARGO_LEGACY_NAME => RefTypesCarrier::CHEAP_CARGO,
        self::CARRIER_DHL_EUROPLUS_LEGACY_NAME => RefTypesCarrier::DHL_EUROPLUS,
        self::CARRIER_DHL_FOR_YOU_LEGACY_NAME => RefTypesCarrier::DHL_FOR_YOU,
        self::CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME => RefTypesCarrier::DHL_PARCEL_CONNECT,
        self::CARRIER_DPD_LEGACY_NAME        => RefTypesCarrier::DPD,
        self::CARRIER_POSTNL_LEGACY_NAME     => RefTypesCarrier::POSTNL,
        self::CARRIER_TRUNKRS_LEGACY_NAME    => RefTypesCarrier::TRUNKRS,
    ];

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
}
