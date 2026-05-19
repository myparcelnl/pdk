<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;

/**
 * @property int                    $id
 * @property int                    $accountId
 * @property int                    $platformId
 * @property string                 $name
 * @property bool                   $hidden
 * @property array<string, mixed>   $billing
 * @property CarrierCollection      $carriers
 * @property string|null            $defaultCarrier
 * @property array<string, mixed>   $deliveryAddress
 * @property array<string, mixed>   $generalSettings
 * @property array<string, mixed>   $return
 * @property array<string, mixed>   $shipmentOptions
 * @property array<string, mixed>[] $trackTrace
 * @property-read Carrier|null      $defaultCarrierModel
 */
class Shop extends Model
{
    public $attributes = [
        'id'              => null,
        'accountId'       => null,
        'platformId'      => null,
        'name'            => null,
        'hidden'          => false,
        'billing'         => [],
        'carriers'        => CarrierCollection::class,
        'defaultCarrier'  => null,
        'deliveryAddress' => [],
        'generalSettings' => [],
        'return'          => [],
        'shipmentOptions' => [],
        'trackTrace'      => [],
    ];

    protected $casts = [
        'id'              => 'int',
        'accountId'       => 'int',
        'platformId'      => 'int',
        'name'            => 'string',
        'hidden'          => 'bool',
        'billing'         => 'array',
        'carriers'        => CarrierCollection::class,
        'defaultCarrier'  => 'string',
        'deliveryAddress' => 'array',
        'generalSettings' => 'array',
        'return'          => 'array',
        'shipmentOptions' => 'array',
        'trackTrace'      => 'array',
    ];

    public function getDefaultCarrierModelAttribute(): ?Carrier
    {
        if (! $this->defaultCarrier) {
            return null;
        }

        return Pdk::get(CarrierRepositoryInterface::class)->find($this->defaultCarrier);
    }
}
