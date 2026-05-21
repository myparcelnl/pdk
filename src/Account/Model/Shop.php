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
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use RuntimeException;

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

    /**
     * Resolve the current shop's default carrier or throw when none is available.
     *
     * Consolidates the "no stored carrier → fall back to the shop default" pattern shared by
     * Shipment and DeliveryOptions: callers that contract a non-nullable Carrier delegate the
     * null-handling here so the trail back to the underlying state is consistent.
     *
     * @throws RuntimeException When no current shop is available, the shop has no default
     *                          carrier set, or the stored V2 name is not in the repository.
     */
    public static function getDefaultCarrierOrThrow(): Carrier
    {
        $shop    = AccountSettings::getShop();
        $default = $shop ? $shop->defaultCarrierModel : null;

        if ($default !== null) {
            return $default;
        }

        if ($shop && $shop->defaultCarrier) {
            throw new RuntimeException(
                sprintf('Default carrier "%s" is not available in the repository', $shop->defaultCarrier)
            );
        }

        throw new RuntimeException('No default carrier available; shop has no default carrier set');
    }
}
