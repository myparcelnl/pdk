<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentLocationType;
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
        foreach ([$this->getCheckoutTypes(), $this->getCoreApiTypes()] as $types) {
            if (array_key_exists($type, $types)) {
                $this->attributes['type'] = $types[$type];

                return $this;
            }
        }

        if (null !== $type && ! in_array($type, (new PickupAnyOfLocation())->getTypeAllowableValues(), true)) {
            Logger::warning('Unknown pickup location type, storing no type', ['type' => $type]);
            $type = null;
        }

        $this->attributes['type'] = $type;

        return $this;
    }

    /**
     * Get the pickup location types the delivery options widget sends in the checkout, as Order API location types.
     *
     * @return array<string, null|string>
     */
    private function getCheckoutTypes(): array
    {
        return [
            'default' => null,
            'locker'  => PickupAnyOfLocation::TYPE_PARCEL_LOCKER,
        ];
    }

    /**
     * Get the location types the Core API returns for pickup locations and drop-off points, as Order API location types.
     *
     * @return array<string, null|string>
     */
    private function getCoreApiTypes(): array
    {
        return [
            RefShipmentLocationType::LOCKER      => PickupAnyOfLocation::TYPE_PARCEL_LOCKER,
            RefShipmentLocationType::POST_OFFICE => PickupAnyOfLocation::TYPE_POST_OFFICE,
            // Core API "retail" covers bpost post point, click & collect and parcel point, so it has no single Order API type.
            RefShipmentLocationType::RETAIL      => null,
        ];
    }
}
