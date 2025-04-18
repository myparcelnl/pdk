<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Base\Concern\DeprecatedAddressProperties;
use MyParcelNL\Pdk\Base\Support\Utils;

/**
 * Address based on API Address Object Definition
 * @see https://developer.myparcel.nl/api-reference/07.shipment-related-object-definitions.html#_7-b-1
 *
 * @property null|string $boxNumber Only applicable to Belgian addresses
 * @property null|string $cc Country code
 * @property null|string $city
 * @property null|string $number
 * @property null|string $numberSuffix
 * @property null|string $postalCode
 * @property null|string $region The region, department, state or province of the address.
 * @property null|string $state Depending on the carrier and destination this field should contain address state. Up to 2 characters long.
 * @property null|string $street
 * @property null|string $streetAdditionalInfo
 */
class Address extends Model
{
    use DeprecatedAddressProperties;

    private const ADDRESS1 = 'address1';
    private const ADDRESS2 = 'address2';
    private const AREA = 'area';

    protected $attributes = [
        'boxNumber'     => null,
        'cc'            => null,
        'city'          => null,
        'number'        => null,
        'numberSuffix'  => null,
        'postalCode'    => null,
        'region'        => null,
        'state'         => null,
        'street'        => null,
        'streetAdditionalInfo' => null,
    ];

    protected $casts = [
        'boxNumber'     => 'string',
        'cc'            => 'string',
        'city'          => 'string',
        'number'        => 'string',
        'numberSuffix'  => 'string',
        'postalCode'    => 'string',
        'region'        => 'string',
        'state'         => 'string',
        'street'        => 'string',
        'streetAdditionalInfo' => 'string',
    ];

    /*
    * Deprecated fields that can be mapped to a new field 1 on 1.
    */
    protected $deprecated = [
        'fullStreet' => 'street',
    ];

    /**
     * Deprecated fields that can't be mapped to a new field 1 on 1.
     *
     * @var string[]
     */
    private $additionalDeprecated = [
        self::ADDRESS1,
        self::ADDRESS2,
        self::AREA,
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $convertedData = $data ? $this->handleDeprecatedFields(Utils::changeArrayKeysCase($data)) : $data;

        parent::__construct($convertedData);
    }

    /**
     * @param  null|array $data
     *
     * @return null|array
     */
    private function handleDeprecatedFields(?array $data): ?array
    {
        // Merge address1 and 2 into "street" and then put address2 also as "streetAdditionalInfo"
        if (!isset($data['street']) && (isset($data['address1']) || isset($data['address2']))) {
            $data['street'] = trim(implode(' ', [$data['address1'], $data['address2']])) ?: null;
            if (isset($data['address1']) && isset($data['address2'])) {
                $data['streetAdditionalInfo'] = $data['address2'];
            }
        }

        foreach ($this->additionalDeprecated as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
                $this->logDeprecationWarning(
                    sprintf('%s', $field),
                    'street, number, numberSuffix, streetAdditionalInfo',
                );
            }
        }

        return $data;
    }
}
