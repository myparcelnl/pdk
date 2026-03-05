<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface;
use MyParcelNL\Pdk\Base\Model\SdkBackedModel;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrierV2;

/**
 * Intantiate a Carrier model based on existing known data when passed an ID/Name, or creates a new Carrier model based on the data passed to the constructor.
 * If nothing is passed, the configured default carrier is returned.
 *
 * This Carrier model is modelled on top of the carrier as returned by the shipments/capabilities endpoint with additional metadata.
 * This gives us the relevant information about the carrier that we need to use, where the other API endpoints only concern themselves with being passed the ID/Name of the carrier.
 *
 * @property null|int    $id      The numeric ID of the carrier, as returned by the v1 API
 * @property null|string $name    The CONSTANT_CASE machine name of the carrier, as returned by the v2 API
 * @property null|string $human   The human readable name of the carrier, as returned by the API
 * @property bool        $enabled Whether or not the carrier is enabled in the plugin settings
 *
 * Properties from the backing RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2 SDK model
 * @property mixed|null  $carrier          Carrier info from contract definitions
 * @property array|null  $packageTypes     Available package types
 * @property mixed|null  $options          Available shipment options
 * @property array|null  $deliveryTypes    Available delivery types
 * @property array|null  $transactionTypes Available transaction types
 * @property mixed|null  $collo            Collo constraints
 */
class Carrier extends SdkBackedModel
{
    /**
     * @deprecated use RefTypesCarrierV2::POSTNL
     */
    public const CARRIER_POSTNL_LEGACY_NAME          = 'postnl';

    /**
     * @deprecated use RefTypesCarrierV2::BPOST
     */
    public const CARRIER_BPOST_LEGACY_NAME           = 'bpost';

    /**
     * @deprecated use RefTypesCarrierV2::CHEAP_CARGO
     */
    public const CARRIER_CHEAP_CARGO_LEGACY_NAME     = 'cheapcargo';

    /**
     * @deprecated use RefTypesCarrierV2::DPD
     */
    public const CARRIER_DPD_LEGACY_NAME             = 'dpd';

    /**
     * @deprecated use RefTypesCarrierV2::BOL
     */
    public const CARRIER_BOL_COM_LEGACY_NAME         = 'bol.com';

    /**
     * @deprecated use RefTypesCarrierV2::DHL_FOR_YOU
     */
    public const CARRIER_DHL_FOR_YOU_LEGACY_NAME     = 'dhlforyou';

    /**
     * @deprecated use RefTypesCarrierV2::DHL_PARCEL_CONNECT
     */
    public const CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME = 'dhlparcelconnect';

    /**
     * @deprecated use RefTypesCarrierV2::DHL_EUROPLUS
     */
    public const CARRIER_DHL_EUROPLUS_LEGACY_NAME    = 'dhleuroplus';

    /**
     * @deprecated use RefTypesCarrierV2::UPS_STANDARD
     */
    public const CARRIER_UPS_STANDARD_LEGACY_NAME = 'upsstandard';

    /**
     * @deprecated use RefTypesCarrierV2::UPS_EXPRESS_SAVER
     */
    public const CARRIER_UPS_EXPRESS_SAVER_LEGACY_NAME = 'upsexpresssaver';

    /**
     * @deprecated use RefTypesCarrierV2::GLS
     */
    public const CARRIER_GLS_LEGACY_NAME       = 'gls';

    /**
     * @deprecated use RefTypesCarrierV2::BRT
     */
    public const CARRIER_BRT_LEGACY_NAME       = 'brt';

    /**
     * @deprecated use RefTypesCarrierV2::TRUNKRS
     */
    public const CARRIER_TRUNKRS_LEGACY_NAME   = 'trunkrs';

    /**
     * @deprecated use new carrier names directly
     */
    public const CARRIER_NAME_TO_LEGACY_MAP = [
        RefTypesCarrierV2::BOL            => self::CARRIER_BOL_COM_LEGACY_NAME,
        RefTypesCarrierV2::BPOST          => self::CARRIER_BPOST_LEGACY_NAME,
        RefTypesCarrierV2::CHEAP_CARGO    => self::CARRIER_CHEAP_CARGO_LEGACY_NAME,
        RefTypesCarrierV2::DHL_EUROPLUS   => self::CARRIER_DHL_EUROPLUS_LEGACY_NAME,
        RefTypesCarrierV2::DHL_FOR_YOU    => self::CARRIER_DHL_FOR_YOU_LEGACY_NAME,
        RefTypesCarrierV2::DHL_PARCEL_CONNECT => self::CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME,
        RefTypesCarrierV2::DPD            => self::CARRIER_DPD_LEGACY_NAME,
        RefTypesCarrierV2::POSTNL         => self::CARRIER_POSTNL_LEGACY_NAME,
        RefTypesCarrierV2::UPS_STANDARD   => self::CARRIER_UPS_STANDARD_LEGACY_NAME,
        RefTypesCarrierV2::UPS_EXPRESS_SAVER => self::CARRIER_UPS_EXPRESS_SAVER_LEGACY_NAME,
        RefTypesCarrierV2::GLS            => self::CARRIER_GLS_LEGACY_NAME,
        RefTypesCarrierV2::BRT            => self::CARRIER_BRT_LEGACY_NAME,
        RefTypesCarrierV2::TRUNKRS        => self::CARRIER_TRUNKRS_LEGACY_NAME,
    ];

    /**
     * Names to ids
     * @deprecated use mapping functionality from the SDK when available
     */
    public const CARRIER_NAME_ID_MAP = [
        RefTypesCarrierV2::BOL            => RefTypesCarrier::BOL,
        RefTypesCarrierV2::BPOST          => RefTypesCarrier::BPOST,
        RefTypesCarrierV2::CHEAP_CARGO    => RefTypesCarrier::CHEAP_CARGO,
        RefTypesCarrierV2::DHL_EUROPLUS   => RefTypesCarrier::DHL_EUROPLUS,
        RefTypesCarrierV2::DHL_FOR_YOU    => RefTypesCarrier::DHL_FOR_YOU,
        RefTypesCarrierV2::DHL_PARCEL_CONNECT => RefTypesCarrier::DHL_PARCEL_CONNECT,
        RefTypesCarrierV2::DPD            => RefTypesCarrier::DPD,
        RefTypesCarrierV2::POSTNL         => RefTypesCarrier::POSTNL,
        RefTypesCarrierV2::GLS            => RefTypesCarrier::GLS,
        RefTypesCarrierV2::UPS_STANDARD   => RefTypesCarrier::UPS_STANDARD,
        RefTypesCarrierV2::UPS_EXPRESS_SAVER => RefTypesCarrier::UPS_EXPRESS_SAVER,
        RefTypesCarrierV2::TRUNKRS        => RefTypesCarrier::TRUNKRS,
    ];

    /**
     * @deprecated use mapping functionality from the SDK when available
     * @see CARRIER_NAME_ID_MAP
     */
    public const CARRIER_LEGACY_NAME_ID_MAP = [
        self::CARRIER_BOL_COM_LEGACY_NAME    => RefTypesCarrier::BOL,
        self::CARRIER_BPOST_LEGACY_NAME      => RefTypesCarrier::BPOST,
        self::CARRIER_CHEAP_CARGO_LEGACY_NAME => RefTypesCarrier::CHEAP_CARGO,
        self::CARRIER_DHL_EUROPLUS_LEGACY_NAME => RefTypesCarrier::DHL_EUROPLUS,
        self::CARRIER_DHL_FOR_YOU_LEGACY_NAME => RefTypesCarrier::DHL_FOR_YOU,
        self::CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME => RefTypesCarrier::DHL_PARCEL_CONNECT,
        self::CARRIER_DPD_LEGACY_NAME        => RefTypesCarrier::DPD,
        self::CARRIER_POSTNL_LEGACY_NAME     => RefTypesCarrier::POSTNL,
        self::CARRIER_TRUNKRS_LEGACY_NAME    => RefTypesCarrier::TRUNKRS,
    ];

    protected $sdkModelClass = RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2::class;

    protected $attributes = [];

    protected $casts = [];

    /**
     * If carrier ID and/or name are given, look up an existing carrier configuration for the current account and instantiate with that data.
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        /** @var AccountSettingsServiceInterface $accountSettings */
        $accountSettings = Pdk::get(AccountSettingsServiceInterface::class);

        $carrierName = null;

        // Determine carrier name from input
        if (isset($data['name'])) {
            $carrierName = $data['name'];
        } elseif (isset($data['id'])) {
            // Convert ID to name using the map
            $carrierName = array_search($data['id'], self::CARRIER_NAME_ID_MAP, true) ?: null;
        }

        // Look up the carrier by name. If no existing carrier was found, simply return a model with the passed data (if any)
        $existing = null;
        if ($carrierName) {
            $found = $accountSettings->getCarriers()->firstWhere('name', $carrierName);

            if ($found) {
                $existing = $found->getAttributes();
            }
        }

        // Merges passed data with found data (if any), giving priority to passed data in case of overlap
        parent::__construct(array_replace($existing ?? [], $data ?? []));
    }
}
