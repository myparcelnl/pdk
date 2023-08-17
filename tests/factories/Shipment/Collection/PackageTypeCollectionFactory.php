<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Shipment\Model\PackageTypeFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of PackageTypeCollection
 * @method PackageTypeCollection make()
 */
final class PackageTypeCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return PackageTypeCollection::class;
    }

    protected function getModelFactory(): string
    {
        return PackageTypeFactory::class;
    }
}
