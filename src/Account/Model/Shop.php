<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Account\Collection\ShopCarrierConfigurationCollection;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

/**
 * @property int                                $id
 * @property int                                $accountId
 * @property int                                $platformId
 * @property string                             $name
 * @property bool                               $hidden
 * @property array<string, mixed>               $billing
 * @property array<string, mixed>               $deliveryAddress
 * @property array<string, mixed>               $generalSettings
 * @property array<string, mixed>               $return
 * @property array<string, mixed>               $shipmentOptions
 * @property array<string, mixed>[]             $trackTrace
 * @property CarrierCollection                  $carriers
 * @property ShopCarrierConfigurationCollection $carrierConfigurations
 */
class Shop extends Model
{
    private const DEPRECATED_KEY_CARRIER_OPTIONS = 'carrierOptions';

    public    $attributes = [
        'id'                    => null,
        'accountId'             => null,
        'platformId'            => null,
        'name'                  => null,
        'hidden'                => false,
        'billing'               => [],
        'deliveryAddress'       => [],
        'generalSettings'       => [],
        'return'                => [],
        'shipmentOptions'       => [],
        'trackTrace'            => [],
        'carriers'              => CarrierCollection::class,
        'carrierConfigurations' => ShopCarrierConfigurationCollection::class,
    ];

    protected $casts      = [
        'id'                    => 'int',
        'accountId'             => 'int',
        'platformId'            => 'int',
        'name'                  => 'string',
        'hidden'                => 'bool',
        'billing'               => 'array',
        'deliveryAddress'       => 'array',
        'generalSettings'       => 'array',
        'return'                => 'array',
        'shipmentOptions'       => 'array',
        'trackTrace'            => 'array',
        'carriers'              => CarrierCollection::class,
        'carrierConfigurations' => ShopCarrierConfigurationCollection::class,
    ];

    protected $deprecated = [
        self::DEPRECATED_KEY_CARRIER_OPTIONS => 'carriers',
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        if (isset($data[self::DEPRECATED_KEY_CARRIER_OPTIONS])) {
            $data['carriers'] = array_map(
                static function (array $carrierOptions): array {
                    $rest = Arr::except($carrierOptions, ['carrier']);

                    return array_merge($rest, $carrierOptions['carrier'] ?? null);
                },
                $data[self::DEPRECATED_KEY_CARRIER_OPTIONS]
            );

            unset($data[self::DEPRECATED_KEY_CARRIER_OPTIONS]);
        }

        parent::__construct($data);
    }

    /**
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toStorableArray(): array
    {
        $carriers = (new Collection($this->carriers))
            ->map(static function (Carrier $carrier): array {
                return $carrier->only([
                    'externalIdentifier',
                    'enabled',
                    'label',
                    'primary',
                    'optional',
                ], Arrayable::STORABLE_NULL);
            });

        return array_replace(
            $this->except('carriers', Arrayable::STORABLE_NULL),
            [
                'carriers' => $carriers->toArray(self::STORABLE_NULL),
            ]
        );
    }
}
