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
     * The allowedShippingMethods setting maps each key (a package type name or
     * TriStateService::INHERIT) to a list of shipping method IDs assigned to it.
     * A single shipping method may appear under multiple keys — all matches contribute.
     *
     * - Setting empty (unconfigured): all carrier-supported package types (legacy default)
     * - Configured but method not in any list: empty collection (off)
     * - Method assigned to INHERIT (alone or alongside specific types): all carrier-supported package types
     * - Method assigned to one or more specific package types: each of those package types
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\PackageTypeCollection
     */
    public function getAllowedPackageTypesAttribute(): PackageTypeCollection
    {
        $allowedShippingMethods = Settings::get(
            CheckoutSettings::ALLOWED_SHIPPING_METHODS,
            CheckoutSettings::ID
        ) ?? [];

        if (empty($allowedShippingMethods)) {
            return $this->getAllCarrierPackageTypes();
        }

        $matchedKeys = [];

        foreach ($allowedShippingMethods as $key => $shippingMethodIds) {
            if (is_array($shippingMethodIds) && in_array((string) $this->id, $shippingMethodIds, true)) {
                $matchedKeys[] = (string) $key;
            }
        }

        if (empty($matchedKeys)) {
            return new PackageTypeCollection();
        }

        if (in_array((string) TriStateService::INHERIT, $matchedKeys, true)) {
            return $this->getAllCarrierPackageTypes();
        }

        $packageTypes = [];

        foreach ($matchedKeys as $matchedKey) {
            $id = DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP[$matchedKey] ?? null;

            if (null !== $id) {
                $packageTypes[] = ['name' => $matchedKey, 'id' => $id];
            }
        }

        return new PackageTypeCollection($packageTypes);
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
