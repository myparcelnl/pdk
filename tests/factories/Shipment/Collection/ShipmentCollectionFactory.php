<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Shipment\Model\ShipmentFactory;
use MyParcelNL\Pdk\Tests\Factory\Collection\AbstractCollectionFactory;

/**
 * @template T of ShipmentCollection
 * @method ShipmentCollection make()
 */
final class ShipmentCollectionFactory extends AbstractCollectionFactory
{
    public function getCollection(): string
    {
        return ShipmentCollection::class;
    }

    protected function getModelFactory(): string
    {
        return ShipmentFactory::class;
    }
}
