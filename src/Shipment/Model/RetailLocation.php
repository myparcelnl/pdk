<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\PickupAnyOfLocation;

/**
 * @property string|null $locationCode
 * @property string|null $locationName
 * @property string|null $retailNetworkId
 * @property null|string $boxNumber
 * @property null|string $cc
 * @property null|string $city
 * @property null|string $number
 * @property null|string $numberSuffix
 * @property null|string $postalCode
 * @property null|string $region
 * @property null|string $state
 * @property null|string $street
 * @property null|string $type
 */
class RetailLocation extends Model
{
    // Pickup location types as sent by the delivery options widget in the checkout.
    private const CHECKOUT_TYPE_DEFAULT = 'default';
    private const CHECKOUT_TYPE_LOCKER  = 'locker';

    protected $attributes = [
        'locationCode'    => null,
        'locationName'    => null,
        'retailNetworkId' => null,
        'boxNumber'       => null,
        'cc'              => null,
        'city'            => null,
        'number'          => null,
        'numberSuffix'    => null,
        'postalCode'      => null,
        'region'          => null,
        'state'           => null,
        'street'          => null,
        'type'            => null,
    ];

    protected $casts      = [
        'locationCode'    => 'string',
        'locationName'    => 'string',
        'retailNetworkId' => 'string',
        'boxNumber'       => 'string',
        'cc'              => 'string',
        'city'            => 'string',
        'number'          => 'string',
        'numberSuffix'    => 'string',
        'postalCode'      => 'string',
        'region'          => 'string',
        'state'           => 'string',
        'street'          => 'string',
        'type'            => 'string',
    ];

    /**
     * Store the pickup location type as an Order API location type, or null when the input names no known type.
     *
     * @param  null|string $type
     *
     * @return self
     */
    protected function setTypeAttribute(?string $type): self
    {
        if (self::CHECKOUT_TYPE_LOCKER === $type) {
            $type = PickupAnyOfLocation::TYPE_PARCEL_LOCKER;
        } elseif (self::CHECKOUT_TYPE_DEFAULT === $type) {
            $type = null;
        } elseif (null !== $type && ! in_array($type, (new PickupAnyOfLocation())->getTypeAllowableValues(), true)) {
            Logger::warning('Unknown pickup location type, storing no type', ['type' => $type]);
            $type = null;
        }

        $this->attributes['type'] = $type;

        return $this;
    }
}
