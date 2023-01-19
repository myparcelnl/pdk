<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Repository\CarrierOptionsRepository;
use MyParcelNL\Pdk\Facade\Pdk;

/**
 * @property null|string $externalIdentifier
 * @property null|int    $id
 * @property null|string $name
 * @property null|string $human
 * @property null|int    $subscriptionId
 * @property bool        $enabled
 * @property bool        $primary
 * @property bool        $isDefault
 * @property bool        $optional
 * @property null|string $label
 * @property null|string $type
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
    public const TYPE_CUSTOM = 'custom';
    public const TYPE_MAIN   = 'main';
    /**
     * Special labels
     */
    public const LABEL_DHL_FOR_YOU_COMPLETE_ACCES = 'dhl_for_you_complete_access';

    protected $attributes = [
        'externalIdentifier' => null,
        'id'                 => null,
        'name'               => null,
        'human'              => null,
        'subscriptionId'     => null,
        'enabled'            => false,
        'isDefault'          => false,
        'label'              => null,
        'optional'           => false,
        'primary'            => false,
        'type'               => null,
    ];

    protected $casts      = [
        'externalIdentifier' => 'string',
        'id'                 => 'int',
        'name'               => 'string',
        'human'              => 'string',
        'subscriptionId'     => 'int',
        'enabled'            => 'bool',
        'isDefault'          => 'bool',
        'label'              => 'string',
        'optional'           => 'bool',
        'primary'            => 'bool',
        'type'               => 'string',
    ];

    /**
     * @return array
     */
    public function getAllOptions(): array
    {
        /** @var \MyParcelNL\Pdk\Carrier\Repository\CarrierOptionsRepository $repository */
        $repository = Pdk::get(CarrierOptionsRepository::class);

        $options = $repository->get($this->subscriptionId ?? $this->id ?? $this->name ?? null);

        if (Carrier::CARRIER_DHL_FOR_YOU_NAME === $this->name) {
            $options['capabilities']['shipmentOptions']['sameDayDelivery'] = Carrier::LABEL_DHL_FOR_YOU_COMPLETE_ACCES === $this->label;
        }

        return $options ?? [];
    }

    public function getIdentifier(): string
    {
        $identifier = $this->externalIdentifier;
        if ($identifier) {
            return $identifier;
        }
        $identifier = $this->name;
        if ($this->subscriptionId) {
            $identifier = "{$identifier}_{$this->subscriptionId}";
        }

        if (! $identifier) {
            throw new \RuntimeException('No identifier found for carrier');
        }
        return $identifier;
    }
}