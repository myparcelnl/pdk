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
    private const FIELD_STREET        = 'street';
    private const FIELD_NUMBER        = 'number';
    private const FIELD_NUMBER_SUFFIX = 'numberSuffix';
    private const FIELD_BOX_NUMBER    = 'boxNumber';

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

    protected $deprecated = [
        'fullStreet'           => 'address1',
        'streetAdditionalInfo' => 'address2',
    ];

    /**
     * Deprecated fields that can't be mapped to a new field 1 on 1.
     *
     * @var string[]
     */
    private $additionalDeprecated = [
        self::FIELD_STREET,
        self::FIELD_NUMBER,
        self::FIELD_NUMBER_SUFFIX,
        self::FIELD_BOX_NUMBER,
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
        // if street, number, and optionally (number_suffix or box_number) are set, combine them into address1
        if (isset($data[self::FIELD_STREET], $data[self::FIELD_NUMBER])) {
            $data['address1'] = trim(
                implode(' ', [
                    $data[self::FIELD_STREET],
                    $data[self::FIELD_NUMBER],
                    $data[self::FIELD_NUMBER_SUFFIX] ?? $data[self::FIELD_BOX_NUMBER] ?? '',
                ])
            );

            foreach ($this->additionalDeprecated as $field) {
                unset($data[$field]);
            }

            $this->logDeprecationWarning(
                sprintf(
                    '%s, %s, and optionally %s or %s',
                    self::FIELD_STREET,
                    self::FIELD_NUMBER,
                    self::FIELD_NUMBER_SUFFIX,
                    self::FIELD_BOX_NUMBER
                ),
                'address1'
            );
        }

        return $data;
    }
}
