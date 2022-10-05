<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Address;

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
 */
class RetailLocation extends Address
{
    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = [])
    {
        $this->attributes['locationCode']    = '';
        $this->attributes['locationName']    = null;
        $this->attributes['retailNetworkId'] = null;

        $this->casts['locationCode']    = 'string';
        $this->casts['locationName']    = 'string';
        $this->casts['retailNetworkId'] = 'string';

        parent::__construct($data);

        unset($this->fullStreet, $this->streetAdditionalInfo);
    }
}
