<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Base\Support\Utils;

/**
 * @property null|string $address1
 * @property null|string $address2
 * @property null|string $area
 * @property null|string $cc
 * @property null|string $city
 * @property null|string $postalCode
 * @property null|string $region
 * @property null|string $state
 */
class Address extends Model
{
    protected $attributes = [
        'address1'   => null,
        'address2'   => null,
        'area'       => null,
        'cc'         => null,
        'city'       => null,
        'postalCode' => null,
        'region'     => null,
        'state'      => null,
    ];

    protected $casts      = [
        'address1'   => 'string',
        'address2'   => 'string',
        'area'       => 'string',
        'cc'         => 'string',
        'city'       => 'string',
        'postalCode' => 'string',
        'region'     => 'string',
        'state'      => 'string',
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $convertedData = $data ? Utils::changeArrayKeysCase($data) : $data;

        parent::__construct($convertedData);
    }
}
