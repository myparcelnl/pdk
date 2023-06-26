<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\PackageType;

/**
 * @property PackageType[] $items
 */
final class PackageTypeCollection extends Collection
{
    protected $cast = PackageType::class;

    /**
     * @return self
     */
    public static function fromAll(): self
    {
        $packageTypes = DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP;

        return new self(
            array_map(static function (int $id, string $name) {
                return compact('id', 'name');
            }, $packageTypes, array_keys($packageTypes))
        );
    }

    /**
     * @param  bool $descending
     *
     * @return self
     */
    public function sortBySize(bool $descending = false): self
    {
        $packageTypesBySize = Pdk::get('packageTypesBySize');

        return $this->sortBy(function (PackageType $packageType) use ($packageTypesBySize) {
            return array_search($packageType->name, $packageTypesBySize, true);
        }, SORT_NATURAL, $descending);
    }
}
