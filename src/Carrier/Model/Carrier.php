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
 * @mixin \MyParcelNL\Pdk\Carrier\Concern\HasDeprecatedSubscriptionId
 */
class Carrier extends Model
{
    public const CARRIER_POSTNL_ID                   = 1;
    /**
     * @deprecated use CARRIER_POSTNL_NAME
     */
    public const CARRIER_POSTNL_LEGACY_NAME          = 'postnl';
    public const CARRIER_POSTNL_NAME                 = 'POSTNL';
    public const CARRIER_BPOST_ID                    = 2;
    /**
     * @deprecated use CARRIER_BPOST_NAME
     */
    public const CARRIER_BPOST_LEGACY_NAME           = 'bpost';
    public const CARRIER_BPOST_NAME                  = 'BPOST';
    public const CARRIER_CHEAP_CARGO_ID              = 3;
    /**
     * @deprecated use CARRIER_CHEAP_CARGO_NAME
     */
    public const CARRIER_CHEAP_CARGO_LEGACY_NAME     = 'cheapcargo';
    public const CARRIER_CHEAP_CARGO_NAME            = 'CHEAP_CARGO';
    public const CARRIER_DPD_ID                      = 4;
    /**
     * @deprecated use CARRIER_DPD_NAME
     */
    public const CARRIER_DPD_LEGACY_NAME             = 'dpd';
    public const CARRIER_DPD_NAME                    = 'DPD';
    public const CARRIER_INSTABOX_ID                 = 5;
    /**
     * @deprecated use CARRIER_INSTABOX_NAME
     */
    public const CARRIER_INSTABOX_LEGACY_NAME        = 'instabox';
    public const CARRIER_INSTABOX_NAME               = 'INSTABOX';
    public const CARRIER_DHL_ID                      = 6;
    /**
     * @deprecated use CARRIER_DHL_NAME
     */
    public const CARRIER_DHL_LEGACY_NAME             = 'dhl';
    public const CARRIER_DHL_NAME                    = 'DHL';
    public const CARRIER_BOL_COM_ID                  = 7;
    /**
     * @deprecated use CARRIER_BOL_COM_NAME
     */
    public const CARRIER_BOL_COM_LEGACY_NAME         = 'bol.com';
    public const CARRIER_BOL_COM_NAME                = 'BOL';
    /**
     * @deprecated Use CARRIER_UPS_STANDARD_ID or CARRIER_UPS_EXPRESS_SAVER_ID instead
     */
    public const CARRIER_UPS_ID                      = 8;
    /**
     * @deprecated Use CARRIER_UPS_STANDARD_NAME or CARRIER_UPS_EXPRESS_SAVER_NAME instead
     */
    public const CARRIER_UPS_LEGACY_NAME             = 'ups';
    public const CARRIER_UPS_NAME                    = 'UPS';
    public const CARRIER_DHL_FOR_YOU_ID              = 9;
    /**
     * @deprecated use CARRIER_DHL_FOR_YOU_NAME
     */
    public const CARRIER_DHL_FOR_YOU_LEGACY_NAME     = 'dhlforyou';
    public const CARRIER_DHL_FOR_YOU_NAME            = 'DHL_FOR_YOU';
    public const CARRIER_DHL_PARCEL_CONNECT_ID       = 10;
    /**
     * @deprecated use CARRIER_DHL_PARCEL_CONNECT_NAME
     */
    public const CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME = 'dhlparcelconnect';
    public const CARRIER_DHL_PARCEL_CONNECT_NAME     = 'DHL_PARCEL_CONNECT';
    public const CARRIER_DHL_EUROPLUS_ID             = 11;
    /**
     * @deprecated use CARRIER_DHL_EUROPLUS_NAME
     */
    public const CARRIER_DHL_EUROPLUS_LEGACY_NAME    = 'dhleuroplus';
    public const CARRIER_DHL_EUROPLUS_NAME           = 'DHL_EUROPLUS';
    public const CARRIER_UPS_STANDARD_ID         = 12;

    /**
     * @deprecated use CARRIER_UPS_STANDARD_NAME
     */
    public const CARRIER_UPS_STANDARD_LEGACY_NAME = 'upsstandard';
    public const CARRIER_UPS_STANDARD_NAME       = 'UPS_STANDARD';
    public const CARRIER_UPS_EXPRESS_SAVER_ID    = 13;

    /**
     * @deprecated use CARRIER_UPS_EXPRESS_SAVER_NAME
     */
    public const CARRIER_UPS_EXPRESS_SAVER_LEGACY_NAME = 'upsexpresssaver';
    public const CARRIER_UPS_EXPRESS_SAVER_NAME  = 'UPS_EXPRESS_SAVER';
    public const CARRIER_GLS_ID                  = 14;

    /**
     * @deprecated use CARRIER_GLS_NAME
     */
    public const CARRIER_GLS_LEGACY_NAME       = 'gls';
    public const CARRIER_GLS_NAME                = 'GLS';

    /**
     * @deprecated use CARRIER_BRT_NAME
     */
    public const CARRIER_BRT_LEGACY_NAME       = 'brt';
    public const CARRIER_BRT_NAME                = 'BRT';
    public const CARRIER_BRT_ID                  = 15;

    /**
     * @deprecated use new carrier names directly
     */
    public const CARRIER_NAME_TO_LEGACY_MAP = [
        self::CARRIER_BOL_COM_NAME            => self::CARRIER_BOL_COM_LEGACY_NAME,
        self::CARRIER_BPOST_NAME              => self::CARRIER_BPOST_LEGACY_NAME,
        self::CARRIER_CHEAP_CARGO_NAME        => self::CARRIER_CHEAP_CARGO_LEGACY_NAME,
        self::CARRIER_DHL_EUROPLUS_NAME       => self::CARRIER_DHL_EUROPLUS_LEGACY_NAME,
        self::CARRIER_DHL_FOR_YOU_NAME        => self::CARRIER_DHL_FOR_YOU_LEGACY_NAME,
        self::CARRIER_DHL_NAME                => self::CARRIER_DHL_LEGACY_NAME,
        self::CARRIER_DHL_PARCEL_CONNECT_NAME => self::CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME,
        self::CARRIER_DPD_NAME                => self::CARRIER_DPD_LEGACY_NAME,
        self::CARRIER_INSTABOX_NAME           => self::CARRIER_INSTABOX_LEGACY_NAME,
        self::CARRIER_POSTNL_NAME             => self::CARRIER_POSTNL_LEGACY_NAME,
        self::CARRIER_UPS_NAME                => self::CARRIER_UPS_LEGACY_NAME,
        self::CARRIER_UPS_STANDARD_NAME       => self::CARRIER_UPS_STANDARD_LEGACY_NAME,
        self::CARRIER_UPS_EXPRESS_SAVER_NAME  => self::CARRIER_UPS_EXPRESS_SAVER_LEGACY_NAME,
        self::CARRIER_GLS_NAME                => self::CARRIER_GLS_LEGACY_NAME,
        self::CARRIER_BRT_NAME                => self::CARRIER_BRT_LEGACY_NAME
    ];

    /**
     * Names to ids
     */
    public const CARRIER_NAME_ID_MAP = [
        self::CARRIER_BOL_COM_NAME            => self::CARRIER_BOL_COM_ID,
        self::CARRIER_BPOST_NAME              => self::CARRIER_BPOST_ID,
        self::CARRIER_CHEAP_CARGO_NAME        => self::CARRIER_CHEAP_CARGO_ID,
        self::CARRIER_DHL_EUROPLUS_NAME       => self::CARRIER_DHL_EUROPLUS_ID,
        self::CARRIER_DHL_FOR_YOU_NAME        => self::CARRIER_DHL_FOR_YOU_ID,
        self::CARRIER_DHL_NAME                => self::CARRIER_DHL_ID,
        self::CARRIER_DHL_PARCEL_CONNECT_NAME => self::CARRIER_DHL_PARCEL_CONNECT_ID,
        self::CARRIER_DPD_NAME                => self::CARRIER_DPD_ID,
        self::CARRIER_INSTABOX_NAME           => self::CARRIER_INSTABOX_ID,
        self::CARRIER_POSTNL_NAME             => self::CARRIER_POSTNL_ID,
        self::CARRIER_GLS_NAME                => self::CARRIER_GLS_ID,
        self::CARRIER_UPS_STANDARD_NAME       => self::CARRIER_UPS_STANDARD_ID,
        self::CARRIER_UPS_EXPRESS_SAVER_NAME  => self::CARRIER_UPS_EXPRESS_SAVER_ID,
    ];

    /**
     * @deprecated use CARRIER_NAME_ID_MAP instead.
     * @see CARRIER_NAME_ID_MAP
     */
    public const CARRIER_LEGACY_NAME_ID_MAP = [
        self::CARRIER_BOL_COM_LEGACY_NAME    => self::CARRIER_BOL_COM_ID,
        self::CARRIER_BPOST_LEGACY_NAME              => self::CARRIER_BPOST_ID,
        self::CARRIER_CHEAP_CARGO_LEGACY_NAME        => self::CARRIER_CHEAP_CARGO_ID,
        self::CARRIER_DHL_EUROPLUS_LEGACY_NAME       => self::CARRIER_DHL_EUROPLUS_ID,
        self::CARRIER_DHL_FOR_YOU_LEGACY_NAME        => self::CARRIER_DHL_FOR_YOU_ID,
        self::CARRIER_DHL_LEGACY_NAME                => self::CARRIER_DHL_ID,
        self::CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME => self::CARRIER_DHL_PARCEL_CONNECT_ID,
        self::CARRIER_DPD_LEGACY_NAME                => self::CARRIER_DPD_ID,
        self::CARRIER_INSTABOX_LEGACY_NAME           => self::CARRIER_INSTABOX_ID,
        self::CARRIER_POSTNL_LEGACY_NAME             => self::CARRIER_POSTNL_ID,
        self::CARRIER_UPS_LEGACY_NAME                => self::CARRIER_UPS_ID,
    ];

    /**
     * Types
     */
    // @deprecated
    public const  TYPE_CUSTOM = 'custom';
    // @deprecated
    public const  TYPE_MAIN   = 'main';

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
        'type'               => self::TYPE_MAIN,
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
        'type'               => 'string',
        'inboundFeatures'    => PropositionCarrierFeatures::class,
        'outboundFeatures'   => PropositionCarrierFeatures::class,
        'capabilities'        => CarrierCapabilities::class, // @deprecated use outboundFeatures instead
        'returnCapabilities'  => CarrierCapabilities::class, // @deprecated use inboundFeatures instead
        'deliveryOptions'     => 'array',
    ];

    /**
     * @todo remove in v3.0.0
     */
    protected $deprecated = [
        'subscriptionId' => 'contractId',
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
     * @return bool
     * @noinspection PhpUnused
     */
    public function getIsDefaultAttribute(): bool
    {
        return self::TYPE_MAIN === $this->type;
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function getTypeAttribute(): string
    {
        return $this->contractId ? self::TYPE_CUSTOM : self::TYPE_MAIN;
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
