<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use InvalidArgumentException;
use MyParcelNL\Sdk\src\Helper\SplitStreet;

/**
 * @property null|string $boxNumber
 * @property null|string $cc
 * @property null|string $city
 * @property null|string $fullStreet
 * @property null|string $number
 * @property null|string $numberSuffix
 * @property null|string $postalCode
 * @property null|string $region
 * @property null|string $state
 * @property null|string $street
 * @property null|string $streetAdditionalInfo
 */
class Address extends Model
{
    protected $attributes = [
        'boxNumber'            => null,
        'cc'                   => null,
        'city'                 => null,
        'fullStreet'           => null,
        'number'               => null,
        'numberSuffix'         => null,
        'postalCode'           => null,
        'region'               => null,
        'state'                => null,
        'street'               => null,
        'streetAdditionalInfo' => null,
    ];

    protected $casts      = [
        'boxNumber'            => 'string',
        'cc'                   => 'string',
        'city'                 => 'string',
        'fullStreet'           => 'string',
        'number'               => 'string',
        'numberSuffix'         => 'string',
        'postalCode'           => 'string',
        'region'               => 'string',
        'state'                => 'string',
        'street'               => 'string',
        'streetAdditionalInfo' => 'string',
    ];

    /**
     * @param  null|string $fullStreet
     *
     * @return self
     * @throws \Exception
     * @noinspection PhpUnused
     */
    public function setFullStreetAttribute(?string $fullStreet): self
    {
        $this->attributes['fullStreet'] = $fullStreet;

        if (! $fullStreet) {
            return $this;
        }

        if (! $this->cc) {
            throw new InvalidArgumentException('First set "cc" before setting "fullStreet".');
        }

        $splitStreet                      = SplitStreet::splitStreet($fullStreet, $this->cc, $this->cc);
        $this->attributes['street']       = $splitStreet->getStreet();
        $this->attributes['number']       = $splitStreet->getNumber();
        $this->attributes['boxNumber']    = $splitStreet->getBoxNumber();
        $this->attributes['numberSuffix'] = $splitStreet->getNumberSuffix();

        return $this;
    }
}
