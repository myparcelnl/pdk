<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;

/**
 * @property string                   $externalIdentifier
 * @property null|int                 $id
 * @property null|string              $name
 * @property null|string              $human
 * @property null|int                 $subscriptionId
 * @property bool                     $enabled
 * @property bool                     $primary
 * @property bool                     $isDefault
 * @property bool                     $optional
 * @property null|string              $label
 * @property null|string              $type
 * @property null|CarrierCapabilities $capabilities
 * @property null|CarrierCapabilities $returnCapabilities
 */
class Carrier extends Model
{
    public const CARRIER_POSTNL_ID               = 1;
    public const CARRIER_POSTNL_NAME             = 'postnl';
    public const CARRIER_BPOST_ID                = 2;
    public const CARRIER_BPOST_NAME              = 'bpost';
    public const CARRIER_CHEAP_CARGO_ID          = 3;
    public const CARRIER_CHEAP_CARGO_NAME        = 'cheapcargo';
    public const CARRIER_DPD_ID                  = 4;
    public const CARRIER_DPD_NAME                = 'dpd';
    public const CARRIER_INSTABOX_ID             = 5;
    public const CARRIER_INSTABOX_NAME           = 'instabox';
    public const CARRIER_DHL_ID                  = 6;
    public const CARRIER_DHL_NAME                = 'dhl';
    public const CARRIER_BOL_COM_ID              = 7;
    public const CARRIER_BOL_COM_NAME            = 'bol.com';
    public const CARRIER_UPS_ID                  = 8;
    public const CARRIER_UPS_NAME                = 'ups';
    public const CARRIER_DHL_FOR_YOU_ID          = 9;
    public const CARRIER_DHL_FOR_YOU_NAME        = 'dhlforyou';
    public const CARRIER_DHL_PARCEL_CONNECT_ID   = 10;
    public const CARRIER_DHL_PARCEL_CONNECT_NAME = 'dhlparcelconnect';
    public const CARRIER_DHL_EUROPLUS_ID         = 11;
    public const CARRIER_DHL_EUROPLUS_NAME       = 'dhleuroplus';
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
        self::CARRIER_UPS_NAME                => self::CARRIER_UPS_ID,
    ];
    /**
     * Types
     */
    public const  TYPE_CUSTOM = 'custom';
    public const  TYPE_MAIN   = 'main';

    protected $attributes = [
        'externalIdentifier' => null,
        'id'                 => null,
        'name'               => null,
        'human'              => null,
        'subscriptionId'     => null,
        'enabled'            => false,
        'isDefault'          => true,
        'label'              => null,
        'optional'           => false,
        'primary'            => false,
        'type'               => self::TYPE_MAIN,
        'capabilities'       => null,
        'returnCapabilities' => null,
    ];

    protected $casts      = [
        'externalIdentifier' => 'string',
        'id'                 => 'int',
        'name'               => 'string',
        'human'              => 'string',
        'subscriptionId'     => 'string',
        'enabled'            => 'bool',
        'isDefault'          => 'bool',
        'label'              => 'string',
        'optional'           => 'bool',
        'primary'            => 'bool',
        'type'               => 'string',
        'capabilities'       => CarrierCapabilities::class,
        'returnCapabilities' => CarrierCapabilities::class,
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        /** @var CarrierRepositoryInterface $repository */
        $repository = Pdk::get(CarrierRepositoryInterface::class);

        if (isset($data['externalIdentifier']) && ! isset($data['name'], $data['id'])) {
            $parts = explode(':', $data['externalIdentifier']);

            $data['name']           = $parts[0] ?? null;
            $data['subscriptionId'] = $parts[1] ?? null;
        }

        if (! isset($data['name'], $data['id'])) {
            $found = $repository->get([
                'id'   => $data['id'] ?? null,
                'name' => $data['name'] ?? Platform::get('defaultCarrier'),
            ]);

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

        if ($this->subscriptionId) {
            $identifier .= ":$this->subscriptionId";
        }

        return $identifier ?: '?';
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
        return $this->subscriptionId ? self::TYPE_CUSTOM : self::TYPE_MAIN;
    }
}
