<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

/**
 * Address based on the Microservice OpenAPI definition:
 * https://address.api.myparcel.nl/openapi.json
 *
 * This definition has a different structure than the attributes sent to the API for orders and shipments and needs to be converted before
 * sending to the API.
 *
 * @property null|string $city
 * @property null|string $countryCode
 * @property null|string $houseNumber
 * @property null|string $houseNumberSuffix
 * @property null|float  $latitude
 * @property null|float  $longitude
 * @property null|string $municipality
 * @property null|string $postalCode
 * @property null|bool   $postOfficeBox
 * @property null|string $street
 *
 * @see MyParcelNL\Pdk\Base\Model\Address
 */
class MyParcelAddress extends Model
{
    protected $attributes = [
        'city'              => null,
        'countryCode'       => null,
        'houseNumber'       => null,
        'houseNumberSuffix' => null,
        'latitude'          => null,
        'longitude'         => null,
        'municipality'      => null,
        'postalCode'        => null,
        'postOfficeBox'     => null,
        'street'            => null,
    ];

    protected $casts = [
        'city'              => 'string',
        'countryCode'       => 'string',
        'houseNumber'       => 'string',
        'houseNumberSuffix' => 'string',
        'latitude'          => 'float',
        'longitude'         => 'float',
        'municipality'      => 'string',
        'postalCode'        => 'string',
        'postOfficeBox'     => 'boolean',
        'street'            => 'string',
    ];


    /**
     * Returns the current model as a MyParcelNL\Pdk\Base\Model\Address object suitable for sending to the API.
     *
     * @return Address
     */
    public function toPdkAddress()
    {
        return new Address([
            'city'          => $this->city,
            'cc'            => $this->countryCode,
            'number'        => $this->houseNumber,
            'numberSuffix'  => $this->houseNumberSuffix,
            'postalCode'    => $this->postalCode,
            'street'        => $this->street
        ]);
    }

}
