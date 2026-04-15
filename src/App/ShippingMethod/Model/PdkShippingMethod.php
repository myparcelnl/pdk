<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\ShippingMethod\Model;

use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Collection\PackageTypeCollection;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * @property string                $id
 * @property string                $name
 * @property string                $description
 * @property bool                  $isEnabled
 * @property bool                  $hasDeliveryOptions
 * @property int                   $minimumDropOffDelay
 * @property PackageTypeCollection $allowedPackageTypes
 * @property Address               $shippingAddress
 * @property bool                  $excludeParcelLockers
 */
class PdkShippingMethod extends Model
{
    protected $attributes = [
        'id'                  => null,
        'name'                => null,
        'description'         => null,
        'allowedPackageTypes' => PackageTypeCollection::class,
        'hasDeliveryOptions'  => true,
        'isEnabled'           => true,
        'minimumDropOffDelay' => null,
        'shippingAddress'     => Address::class,
        'excludeParcelLockers' => false,
    ];

    protected $casts      = [
        'id'                  => 'string',
        'name'                => 'string',
        'description'         => 'string',
        'allowedPackageTypes' => PackageTypeCollection::class,
        'hasDeliveryOptions'  => 'bool',
        'isEnabled'           => 'bool',
        'minimumDropOffDelay' => 'int',
        'shippingAddress'     => Address::class,
        'excludeParcelLockers' => 'bool',
    ];

    protected $deprecated = [
        'allowPackageTypes' => 'allowedPackageTypes',
    ];

    /**
     * Resolve allowed package types from checkout settings for this shipping method.
     *
     * The allowedShippingMethods setting maps shipping method IDs to their configured
     * package type: off (not in map), INHERIT (dynamic from product settings), or a
     * specific package type name (fixed).
     *
     * - Not configured: all carrier-supported package types
     * - INHERIT: all carrier-supported package types
     * - Specific type: only that package type
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\PackageTypeCollection
     */
    public function getAllowedPackageTypesAttribute(): PackageTypeCollection
    {
        $allowedShippingMethods = Settings::get(
            CheckoutSettings::ALLOWED_SHIPPING_METHODS,
            CheckoutSettings::ID
        );

        $configured = $allowedShippingMethods[$this->id] ?? null;

        // Not configured or INHERIT: all carrier-supported package types.
        if (! $configured || TriStateService::INHERIT === $configured) {
            return $this->getAllCarrierPackageTypes();
        }

        // Specific package type configured for this shipping method.
        $id = DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP[$configured] ?? null;

        return new PackageTypeCollection($id ? [['name' => $configured, 'id' => $id]] : []);
    }

    /**
     * Collect all package types supported by any carrier in the account.
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\PackageTypeCollection
     */
    private function getAllCarrierPackageTypes(): PackageTypeCollection
    {
        $v2ToPdkMap = array_flip(DeliveryOptions::PACKAGE_TYPES_V2_MAP);
        $types      = [];

        foreach (Pdk::get(CarrierRepositoryInterface::class)->all() as $carrier) {
            foreach ($carrier->packageTypes ?? [] as $v2PackageType) {
                $name = $v2ToPdkMap[$v2PackageType] ?? null;

                if ($name && ! isset($types[$name])) {
                    $types[$name] = [
                        'name' => $name,
                        'id'   => DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP[$name] ?? null,
                    ];
                }
            }
        }

        return new PackageTypeCollection(array_values($types));
    }
}
