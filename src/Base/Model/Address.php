<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use InvalidArgumentException;
use MyParcelNL\Sdk\src\Helper\SplitStreet;

/**
 * @property null|string $boxNumber
 * @property null|string $country
 * @property null|string $email
 * @property null|string $fullStreet
 * @property null|string $number
 * @property null|string $numberSuffix
 * @property null|string $person
 * @property null|string $phone
 * @property null|string $postalCode
 * @property null|string $region
 * @property null|string $street
 * @property null|string $streetAdditionalInfo
 */
class Address extends Model
{
    protected $attributes = [
        'boxNumber'            => null,
        'city'                 => null,
        'country'              => null,
        'email'                => null,
        'number'               => null,
        'numberSuffix'         => null,
        'person'               => null,
        'phone'                => null,
        'postalCode'           => null,
        'region'               => null,
        'street'               => null,
        'streetAdditionalInfo' => null,
        'fullStreet'           => null,
    ];

    /**
     * @param  null|string $fullStreet
     *
     * @return self
     * @throws \Exception
     */
    public function setFullStreetAttribute(?string $fullStreet): self
    {
        $this->attributes['fullStreet'] = $fullStreet;

        if (! $fullStreet) {
            return $this;
        }

        $country = $this->getCountry();

        if (! $country) {
            throw new InvalidArgumentException('First set "country" before setting "fullStreet".');
        }

        $splitStreet                      = SplitStreet::splitStreet($fullStreet, $country, $country);
        $this->attributes['street']       = $splitStreet->getStreet();
        $this->attributes['number']       = $splitStreet->getNumber();
        $this->attributes['boxNumber']    = $splitStreet->getBoxNumber();
        $this->attributes['numberSuffix'] = $splitStreet->getNumberSuffix();

        return $this;
    }
}
