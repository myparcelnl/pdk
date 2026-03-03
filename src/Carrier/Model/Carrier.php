<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Facade\FrontendData;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrierV2;

/**
 * @property string                   $externalIdentifier
 * @property null|int                 $id
 * @property null|string              $name
 * @property null|string              $human
 * @property null|int                 $contractId
 * @property bool                     $enabled
 * @property bool                     $primary
 * @property bool                     $isCustom
 * @property bool                     $isDefault
 * @property bool                     $optional
 * @property null|string              $label
 * @property null|string              $type
 * @property PropositionCarrierFeatures|null  $inboundFeatures
 * @property PropositionCarrierFeatures|null  $outboundFeatures
 * @property CarrierCapabilities        $capabilities        // @deprecated use outboundFeatures instead
 * @property CarrierCapabilities        $returnCapabilities  // @deprecated use inboundFeatures instead
 * @property null|array                 $deliveryCountries
 * @property null|array                 $pickupCountries
 *
 */
class Carrier extends Model
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
     * @deprecated use CARRIER_NAME_ID_MAP instead.
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


    protected $attributes = [
        'externalIdentifier' => null,
        'id'                 => null,
        'name'               => null,
        'human'              => null,
        'contractId'         => null,
        'enabled'            => true,
        'isDefault'          => true,
        'label'              => null,
        'optional'           => false,
        'primary'            => false,
        'inboundFeatures'    => null,
        'outboundFeatures'   => null,
        'capabilities'        => null, // @deprecated use outboundFeatures instead
        'returnCapabilities'  => null, // @deprecated use inboundFeatures instead
        'deliveryOptions'     => null,
    ];

    protected $casts      = [
        'externalIdentifier' => 'string',
        'id'                 => 'int',
        'name'               => 'string',
        'human'              => 'string',
        'contractId'         => 'string',
        'enabled'            => 'bool',
        'isDefault'          => 'bool',
        'label'              => 'string',
        'optional'           => 'bool',
        'primary'            => 'bool',
        'inboundFeatures'    => PropositionCarrierFeatures::class,
        'outboundFeatures'   => PropositionCarrierFeatures::class,
        'capabilities'        => CarrierCapabilities::class, // @deprecated use outboundFeatures instead
        'returnCapabilities'  => CarrierCapabilities::class, // @deprecated use inboundFeatures instead
        'deliveryOptions'     => 'array',
    ];

    /**
     * If carrier ID and/or name are given, look up an existing carrier configuration from the CarrierRepository and instantiate with that data.
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        /** @var CarrierRepositoryInterface $repository */
        $repository = Pdk::get(CarrierRepositoryInterface::class);

        if (isset($data['externalIdentifier']) && ! isset($data['name'], $data['id'])) {
            $parts = explode(':', $data['externalIdentifier']);

            $data['name']       = $parts[0] ?? null;
            $data['contractId'] = $parts[1] ?? null;
        }

        if (! isset($data['name'], $data['id'])) {
            $carrierInput         = [];
            $carrierInput['id']   = $data['id'] ?? null;
            $carrierInput['name'] = $data['name'] ?? null;

            // If neither the id or name is provided, fallback to the default carrier
            // Prevents the default carrier being returned if an unknown ID is provided
            if (! $carrierInput['id'] && ! $carrierInput['name']) {
                try {
                    $propositionService = Pdk::get(PropositionService::class);
                    $proposition = $propositionService->getPropositionConfig();
                    $defaultCarrier = $propositionService->getDefaultCarrier($proposition);
                    $carrierInput['name'] = $defaultCarrier->name;

                    Logger::warning(
                        'Carrier Name and ID not given, instantiating default Carrier model',
                        [
                            'id'   => $data['id'] ?? null,
                            'name' => $data['name'] ?? null,
                        ]
                    );
                } catch (\Exception $e) {
                    Logger::error('Failed to get default carrier', ['exception' => $e]);
                }
            }
            $found = $repository->get($carrierInput);

            if ($found) {
                $existing = $found->getAttributes();
            }
        }

        parent::__construct(array_replace($existing ?? [], $data ?? []));
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function getExternalIdentifierAttribute(): string
    {
        $identifier = $this->name;

        if ($this->contractId) {
            $identifier .= ":$this->contractId";
        }

        return $identifier ?: '?';
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function getIsCustomAttribute(): bool
    {
        return ! $this->isDefault;
    }
    /**
     * @return string[]
     */
    public function toStorableArray(): array
    {
        return [
            'externalIdentifier' => FrontendData::getLegacyIdentifier($this->externalIdentifier),
        ];
    }
}
