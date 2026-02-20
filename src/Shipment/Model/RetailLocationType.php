<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

/**
 * Possible values for the type attribute of a RetailLocation.
 * @todo Refactor to PHP enum when PHP 7.4 support is dropped
 */
class RetailLocationType
{
    public const CLICK_COLLECT = 'CLICK_COLLECT';
    public const PARCEL_LOCKER = 'PARCEL_LOCKER';
    public const PARCEL_POINT = 'PARCEL_POINT';
    public const POST_OFFICE = 'POST_OFFICE';
    public const POST_POINT = 'POST_POINT';

    public const ALL_TYPES = [
        self::CLICK_COLLECT,
        self::PARCEL_LOCKER,
        self::PARCEL_POINT,
        self::POST_OFFICE,
        self::POST_POINT,
    ];

    protected $attributes = [
        'type' => null,
    ];

    public function __construct(string $type)
    {
        if (! in_array($type, self::ALL_TYPES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid retail location type: %s', $type));
        }

        $this->attributes['type'] = $type;
    }
}
